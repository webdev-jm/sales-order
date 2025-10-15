<?php

namespace App\Http\Controllers;

use App\Models\PPUForm;
use App\Models\PPUFormItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SalesOrderCutOff;
use App\Models\SalesOrder;
use App\Http\Traits\GlobalTrait;
use App\Http\Requests\StorePPUFormRequest;
use App\Http\Requests\UpdatePPUFormRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PPUFormController extends Controller
{
    use GlobalTrait;

    public $setting;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request)
    {
        $logged_account = Session::get('logged_account');
        $search = trim($request->get('search'));


        // $this->checkSalesOrderStatus();
        
        if(isset($logged_account)) {

            $ppu_form = PPUForm::query()
                ->orderByDesc('control_number')
                // Filter by related account or user using search
                ->when(!empty($search), function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        // Search on SalesOrder fields
                        $q->where('control_number', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                        // Search on related account/user
                        $q->orWhereHas('account_login.account', function ($subQ) use ($search) {
                            $subQ->where('account_code', 'like', "%{$search}%")
                                ->orWhere('short_name', 'like', "%{$search}%");
                        })->orWhereHas('account_login.user', function ($subQ) use ($search) {
                            $subQ->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%");
                        });
                    });
                })
                ->when(!auth()->user()->hasRole('superadmin'), function ($query) {
                    $accountIds = auth()->user()->accounts()->pluck('id');
                    $query->whereHas('account_login', function ($q) use ($accountIds) {
                        $q->whereIn('account_id', $accountIds);
                    });
                })
                // Optional eager loading to reduce N+1 issues
                ->with(['account_login.account', 'account_login.user'])
                ->paginate($this->setting->data_per_page)
                ->onEachSide(1)
                ->appends(request()->query());

                return view('ppu-forms.index')->with([
                'search' => $search,
                'ppu_form' => $ppu_form
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating PPU'
            ]);
        }

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $logged_account = Session::get('logged_account');
        $search = trim($request->get('search'));

        if(isset($logged_account)) {

            Session::forget('ppu_item');

            $date = date('Y-m-d');

            $control_number = $this->generateControlNumber();

            $sales_orders = SalesOrder::SalesOrderSearch($search, $logged_account,$this->setting->data_per_page);
            return view('ppu-forms.create')->with([
                'control_number' => $control_number,
                'logged_account' => $logged_account
            ]);
        } else {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating ppu form'
            ]);
        }
    }

    private function generateControlNumber() {
        $date_code = date('Ymd');

        do {
            $control_number = 'PPU-'.$date_code.'-001';
            // get the most recent sales order
            $sales_order = PPUForm::withTrashed()->orderBy('control_number', 'DESC')
                ->first();
            if(!empty($sales_order)) {
                $latest_control_number = $sales_order->control_number;
                list(, $prev_date, $last_number) = explode('-', $latest_control_number);

                // Increment the number based on the date
                $number = ($date_code == $prev_date) ? ((int)$last_number + 1) : 1;

                // Format the number with leading zeros
                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                // Construct the new control number
                $control_number = "PPU-$date_code-$formatted_number";
            }

        } while(PPUForm::withTrashed()->where('control_number', $control_number)->exists());

        return $control_number;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePPUFormRequest $request)
    {
        $request->control_number = $this->generateControlNumber();

        $logged_account = Session::get('logged_account');
        $ppu_item = Session::get('ppu_item');
        
        $account = $logged_account->account;

        $ppu_form = new PPUForm([
            'account_login_id' => $logged_account->id,
            'control_number' => $request->control_number,
            'date_prepared' => $request->date_prepared,
            'pickup_date' => $request->pickup_date,
            'date_submitted' => $request->date_submitted,
            'status' => $request->status,
            'total_quantity' => $ppu_item['total_qty'] ?? 0,
            'total_amount' => $ppu_item['total_amount'] ?? 0,
        ]);
      
        $names = array_map(fn($item) => strtolower(trim($item['rs'])), $ppu_item['items']);
        if (count($names) !== count(array_unique($names))) {
            return back()->withErrors(['items' => 'Duplicate RTV No. are not allowed.'])->withInput();
        }

        $duplicateNames = DB::table('ppuform_items')
            ->whereIn(DB::raw('LOWER(rtv_number)'), $names)
            ->where('ppuform_id', '!=', $ppu_form->id)
            ->pluck('rtv_number')
            ->map(fn($n) => strtolower($n))
            ->toArray();

        if (!empty($duplicateNames)) {
            $duplicates = implode(', ', array_unique($duplicateNames));
            return back()->withErrors([
                'items' => "The following RTV No. already exist in another PPU: {$duplicates}"
            ])->withInput();
        }

        $ppu_form->save();

        if(!empty($ppu_item)){
            foreach ($ppu_item['items'] as $key => $items){
                $ppuform_item = new PPUFormItem([
                    'ppuform_id' => $ppu_form->id,
                    'rtv_number' => $items['rs'],
                    'rtv_date' => $items['rtv'],
                    'branch_name' =>  $items['name'],
                    'total_quantity' =>  $items['qty'],
                    'total_amount' =>  $items['amount'],
                    'remarks' =>  $items['remarks'],
                ]);
                $ppuform_item->save();
            }
        }
        


        activity('create')
        ->performedOn($ppu_form)
        ->log(':causer.firstname :causer.lastname has created ppu form :subject.control_number');

        return redirect()->route('ppu.index')->with([
            'message_success' => 'PPU '.$ppu_form->control_number.' was created'
        ]);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PPUForm  $pPUForm
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ppu_form = PPUForm::findOrFail($id);
        $items = PPUFormItem::where('ppuform_id', $ppu_form->id)->get();


        return view('ppu-forms.show')->with([
            'ppu_form' => $ppu_form,
            'items' => $items,
     
        ]);
    }

    public function printPDF($id) {
        $ppu_form = PPUForm::findOrFail($id); 

        $ppuform_item = PPUFormItem::where('ppuform_id', $id)->get();  


        $pdf = PDF::loadView('ppu-forms.pdf', [
            'ppu_form' => $ppu_form,
            'ppuform_item'=> $ppuform_item,

        ]);

        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PPUForm  $pPUForm
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $logged_account = Session::get('logged_account');
        $ppu_form = PPUForm::findOrFail($id);

        $ppuform_item = PPUFormItem::where('ppuform_id', $id)->get();  

        $control_number = $ppu_form->control_number;


        return view('ppu-forms.edit')->with([
            'control_number' => $control_number,
            'logged_account' => $logged_account,
            'ppu_form' => $ppu_form,
            'ppuform_item' => $ppuform_item,

     
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PPUForm  $pPUForm
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePPUFormRequest $request, $id)
    {
        $logged_account = Session::get('logged_account');
        $ppu_item = Session::get('ppu_item');

        $ppu_form = PPUForm::findOrFail($id);
        
        $ppu_form->update([
            'account_login_id' => $logged_account->id,
            'control_number' => $request->control_number,
            'date_prepared' => $request->date_prepared,
            'pickup_date' => $request->pickup_date,
            'date_submitted' => $request->date_submitted,
            'status' => $request->status,
            'total_quantity' => $ppu_item['total_qty'] ?? 0,
            'total_amount' => $ppu_item['total_amount'] ?? 0,
        ]);

        $names = array_map(fn($item) => strtolower(trim($item['rs'])), $ppu_item['items']);
        if (count($names) !== count(array_unique($names))) {
            return back()->withErrors(['items' => 'Duplicate RTV No. are not allowed.'])->withInput();
        }

        $duplicateNames = DB::table('ppuform_items')
            ->whereIn(DB::raw('LOWER(rtv_number)'), $names)
            ->where('ppuform_id', '!=', $id)
            ->pluck('rtv_number')
            ->map(fn($n) => strtolower($n))
            ->toArray();

        if (!empty($duplicateNames)) {
            $duplicates = implode(', ', array_unique($duplicateNames));
            return back()->withErrors([
                'items' => "The following RTV No. already exist in another PPU: {$duplicates}"
            ])->withInput();
        }

        DB::table('ppuform_items')->where('ppuform_id', $id)->delete();


        if(!empty($ppu_item)){
            foreach ($ppu_item['items'] as $key => $items){
                $ppuform_item = new PPUFormItem([
                    'ppuform_id' => $ppu_form->id,
                    'rtv_number' => $items['rs'],
                    'rtv_date' => $items['rtv'],
                    'branch_name' =>  $items['name'],
                    'total_quantity' =>  $items['qty'],
                    'total_amount' =>  $items['amount'],
                    'remarks' =>  $items['remarks'],
                ]);
                $ppuform_item->save();
            }
        }

        activity('update')
        ->performedOn($ppu_form)
        ->log(':causer.firstname :causer.lastname has updated ppu form :subject.control_number');

        return redirect()->route('ppu.index')->with([
            'message_success' => 'PPU '.$ppu_form->control_number.' was updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PPUForm  $pPUForm
     * @return \Illuminate\Http\Response
     */
    public function destroy(PPUForm $pPUForm)
    {
        //
    }
}

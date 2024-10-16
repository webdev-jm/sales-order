<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

use App\Models\Brand;
use App\Models\PafBrandApproval;

class BrandApproval extends Component
{
    public $paf;
    public $brands;
    public $brand_approvals;
    public $remarks;

    public function approve($brand_id) {
        $brand_approval = new PafBrandApproval([
            'paf_id' => $this->paf->id,
            'brand_id' => $brand_id,
            'remarks' => $this->remarks[$brand_id] ?? NULL,
        ]);
        $brand_approval->save();
    }

    public function mount($paf) {
        $this->paf = $paf;

        $this->brands = Brand::whereHas('products', function($query) {
                $query->whereHas('paf_details', function($qry) {
                    $qry->where('paf_id', $this->paf->id);
                });
            })
            ->get();

        foreach($this->brands as $brand) {
            $brand_approval = PafBrandApproval::where('paf_id', $this->paf->id)
                ->where('brand_id', $brand->id)
                ->first();
            if(!empty($brand_approval)) {
                $this->brand_approvals[$brand->id] = $brand_approval;
            }
        }
    }

    public function render()
    {
        foreach($this->brands as $brand) {
            $brand_approval = PafBrandApproval::where('paf_id', $this->paf->id)
                ->where('brand_id', $brand->id)
                ->first();
            if(!empty($brand_approval)) {
                $this->brand_approvals[$brand->id] = $brand_approval;
            }
        }

        // check if all brands has been approved
        if($this->brands->count() == count($this->brand_approvals) && $this->paf->status == 'approved') {
            $this->paf->update([
                'status' => 'approved by brand'
            ]);
        }

        return view('livewire.paf.brand-approval');
    }
}

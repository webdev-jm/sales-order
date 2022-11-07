<?php

namespace App\Http\Controllers;

use App\Models\OrganizationStructure;
use App\Http\Requests\StoreOrganizationStructureRequest;
use App\Http\Requests\UpdateOrganizationStructureRequest;

use Illuminate\Http\Request;

class OrganizationStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = trim($request->get('type'));
        if($type == '') {
            $type = 'NKAG';
        }

        $structures = OrganizationStructure::orderBy('reports_to_id', 'DESC')->where('type', $type)->get();
        $data_arr = [];
        foreach($structures as $structure) {
            if(!empty($structure->reports_to_id)) {
                $data_arr[$structure->reports_to_id][] = $structure;
            } else {
                $data_arr['head'] = $structure;
            }
        }
        
        $chart_data = [];
        if(!empty($data_arr)) {
            $chart_data = [
                'id' => 'rootNode',
                'collapsed' => false,
                'verticalLevel' => 5,
                'name' => empty($data_arr['head']->user_id) ? 'Vacant' : $data_arr['head']->user->fullName(),
                'title' => $data_arr['head']->job_title->job_title,
                'relationship' => '001',
            ];
            
            $children = [];
            if(isset($data_arr[$data_arr['head']->id])) {
                foreach($data_arr[$data_arr['head']->id] as $key => $data) {
                    $relationship = '1'.(count($data_arr[$data_arr['head']->id]) > 1 ? '1' : '0');
                    // first level
                    if(isset($data_arr[$data->id]) && count($data_arr[$data->id]) > 0) {
                        $relationship .= '1';
                        $children[$key] = [
                            'name' => empty($data->user_id) ? 'Vacant' : $data->user->fullName(),
                            'title' => $data->job_title->job_title,
                            'relationship' => $relationship
                        ];
                        $child_arr = [];
                        foreach($data_arr[$data->id] as $key2 => $child) {
                            $relationship = '1'.(count($data_arr[$data->id]) > 1 ? '1' : '0');
                             // second level
                            if(isset($data_arr[$child->id]) && count($data_arr[$child->id]) > 0) {
                                $relationship .= '1';
                                $child_arr[$key2] = [
                                    'name' => empty($child->user_id) ? 'Vacant' : $child->user->fullName(),
                                    'title' => $child->job_title->job_title,
                                    'relationship' => $relationship
                                ];
                                $child2_arr = [];
                                foreach($data_arr[$child->id] as $key3 => $child2) {
                                    $relationship = '1'.(count($data_arr[$child->id]) > 1 ? '1' : '0');
                                    // third level
                                    if(isset($data_arr[$child2->id]) && count($data_arr[$child2->id]) > 0) {
                                        $relationship .= '1';
                                        $child2_arr[] = [
                                            'name' => empty($child2->user_id) ? 'Vacant' : $child2->user->fullName(),
                                            'title' => $child2->job_title->job_title,
                                            'relationship' => $relationship
                                        ];
                                        $child3_arr = [];
                                        foreach($data_arr[$child2->id] as $key4 => $child3) {
                                            $relationship = '1'.(count($data_arr[$child2->id]) > 1 ? '1' : '0');
                                            // fourth level
                                            if(isset($data_arr[$child3->id]) && count($data_arr[$child3->id]) > 0) {
                                                $relationship .= '1';
                                                $child3_arr[] = [
                                                    'name' => empty($child3->user_id) ? 'Vacant' : $child3->user->fullName(),
                                                    'title' => $child3->job_title->job_title,
                                                    'relationship' => $relationship
                                                ];
                                                $child4_arr = [];
                                                foreach($data_arr[$child3->id] as $key5 => $child4) {
                                                    $relationship = '1'.(count($data_arr[$child3->id]) > 1 ? '1' : '0');
                                                    // fifth level
                                                    if(isset($data_arr[$child4->id]) && count($data_arr[$child4->id]) > 0) {
                                                        $relationship .= '1';
                                                        $child4_arr[] = [
                                                            'name' => empty($child4->user_id) ? 'Vacant' : $child4->user->fullName(),
                                                            'title' => $child4->job_title->job_title,
                                                            'relationship' => $relationship
                                                        ];
                                                        $child5_arr = [];
                                                        foreach($data_arr[$child4->id] as $key6 => $child5) {
                                                            $relationship = '1'.(count($data_arr[$child4->id]) > 1 ? '1' : '0');
                                                            // sixth level
                                                            if(isset($data_arr[$child5->id]) && count($data_arr[$child5->id]) > 0) {
                                                                $relationship .= '1';
                                                            } else {
                                                                $relationship .= '0';
                                                                $child5_arr[] = [
                                                                    'name' => empty($child5->user_id) ? 'Vacant' : $child5->user->fullName(),
                                                                    'title' => $child5->job_title->job_title,
                                                                    'relationship' => $relationship
                                                                ];
                                                            }
                                                        }
                                                        $child4_arr[$key5]['children'] = $child5_arr;
                                                    } else {
                                                        $relationship .= '0';
                                                        $child4_arr[] = [
                                                            'name' => empty($child4->user_id) ? 'Vacant' : $child4->user->fullName(),
                                                            'title' => $child4->job_title->job_title,
                                                            'relationship' => $relationship
                                                        ];
                                                    }
                                                }
                                                $child3_arr[$key4]['children'] = $child4_arr;

                                            } else {
                                                $relationship .= '0';
                                                $child3_arr[] = [
                                                    'name' => empty($child3->user_id) ? 'Vacant' : $child3->user->fullName(),
                                                    'title' => $child3->job_title->job_title,
                                                    'relationship' => $relationship
                                                ];
                                            }
                                        }
                                        $child2_arr[$key3]['children'] = $child3_arr;
                                    } else {
                                        $relationship .= '0';
                                        $child2_arr[] = [
                                            'name' => empty($child2->user_id) ? 'Vacant' : $child2->user->fullName(),
                                            'title' => $child2->job_title->job_title,
                                            'relationship' => $relationship
                                        ];
                                    }
                                }
                                $child_arr[$key2]['children'] = $child2_arr;
                            } else {
                                $relationship .= '0';
                                $child_arr[] = [
                                    'name' => empty($child->user_id) ? 'Vacant' : $child->user->fullName(),
                                    'title' => $child->job_title->job_title,
                                    'relationship' => $relationship
                                ];
                            }
                            $children[$key]['children'] = $child_arr;
                        }
                    } else {
                        $relationship .= '0';
                        $children[$key] = [
                            'name' => empty($data->user_id) ? 'Vacant' : $data->user->fullName(),
                            'title' => $data->job_title->job_title,
                            'relationship' => $relationship
                        ];
                    }
                }
            }
            $chart_data['children'] = $children;
        }
        
        return view('organization-structures.index')->with([
            'type' => $type,
            'chart_data' => $chart_data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreOrganizationStructureRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrganizationStructureRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrganizationStructure  $organizationStructure
     * @return \Illuminate\Http\Response
     */
    public function show(OrganizationStructure $organizationStructure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrganizationStructure  $organizationStructure
     * @return \Illuminate\Http\Response
     */
    public function edit(OrganizationStructure $organizationStructure)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOrganizationStructureRequest  $request
     * @param  \App\Models\OrganizationStructure  $organizationStructure
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrganizationStructureRequest $request, OrganizationStructure $organizationStructure)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrganizationStructure  $organizationStructure
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrganizationStructure $organizationStructure)
    {
        //
    }
}

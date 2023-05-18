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
        $org_types_arr = [
            'NKAG',
            'RDG',
            'TMG',
            'TEST',
        ];

        $type = trim($request->get('type')) ?: 'NKAG';

        $structures = OrganizationStructure::orderBy('reports_to_id', 'DESC')->where('type', $type)->get();

        $data_arr = [];

        // Loop through each structure and group them by their parent_id
        foreach ($structures as $structure) {
            $parent_id = $structure->reports_to_id ?? 'head'; // If reports_to_id is null, default to 'head'
            $data_arr[$parent_id] = $data_arr[$parent_id] ?? [];
            $data_arr[$parent_id][] = $structure;
        }

        // Generate the chart data recursively starting from the root node
        $chart_data = $this->generateChartData($data_arr, 'head');

        return view('organization-structures.index')->with([
            'type' => $type,
            'chart_data' => $chart_data[0] ?? [],
            'org_types_arr' => $org_types_arr
        ]);
    }

    // Helper function to generate the chart data recursively
    private function generateChartData(array $data_arr, string $parent_id, int $level = 0): array
    {
        $chart_data = [];

        // Loop through each child of the current node (if any) and generate their chart data recursively
        foreach ($data_arr[$parent_id] ?? [] as $key => $data) {
            $relationship = '1' . (count($data_arr[$parent_id]) > 1 ? '1' : '0'); // Determine the relationship between the current node and its parent
            $child_arr = $this->generateChartData($data_arr, $data->id, $level + 1); // Generate the chart data for the current node's children

            // Add the chart data for the current node to the array
            $chart_data[] = [
                'id' => $data->id, // Set the ID of the node
                'collapsed' => false,
                'verticalLevel' => $level, // Set the vertical level of the node
                'name' => empty($data->user_id) ? 'Vacant' : $data->user->fullName(), // If the node has no user, label it as "Vacant"
                'title' => $data->job_title->job_title, // Retrieve the job title for the node
                'relationship' => $relationship, // Set the relationship between the node and its parent
                'children' => $child_arr, // Set the chart data for the node's children
            ];
        }

        // Return the chart data for the current node
        return $chart_data;
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

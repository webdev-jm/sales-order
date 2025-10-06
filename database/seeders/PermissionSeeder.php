<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions_arr = [
            'Credit Memo Reason' => [
                'cm reason access'   => 'access to credit memo reason module',
                'cm reason create'   => 'access to create credit memo reason',
                'cm reason edit'     => 'access to edit credit memo reason',
                'cm reason delete'   => 'access to delete credit memo reason',
            ],
            'Credit Memo'=> [
                'cm access'         => 'access to credit memo module',
                'cm create'         => 'access to create credit memo',
                'cm edit'           => 'access to edit credit memo',
                'cm delete'         => 'access to delete credit memo',
                'cm approve'        => 'access to approve credit memo',
                'cm print'          => 'access to print credit memo',
            ],
            'Sales Order' => [
                'sales order list'          => 'access to sales order lists',
                'sales order change status' => 'access to change upload error status sales orders',
                'sales order access'        => 'access to sales order module',
                'sales order create'        => 'access to create sales order',
                'sales order edit'          => 'access to edit sales order',
                'sales order delete'        => 'access to delete sales order',
            ],
            'Purchase Order' => [
                'purchase order access'     => 'access to purchase order list',
            ],
            'Invoice' => [
                'invoice access'    => 'access to generating invoice data using po number.'
            ],
            'Schedule' => [
                'schedule access'                   => 'access to schedule module',
                'schedule create'                   => 'access to create schedule',
                'schedule list'                     => 'access to view schedule list',
                'schedule reschedule'               => 'access to create reschedule',
                'schedule approve request'          => 'access to approve schedule request',
                'schedule delete request'           => 'access to create delete schedule request',
                'schedule approve reschedule'       => 'access to approve reschedule request',
                'schedule approve delete request'   => 'access to approve delete schedule request',
                'schedule approve deviation'        => 'access to approve deviation request',
            ],
            'Report' => [
                'report access' => 'access to reports',
                'report export' => 'access to export raw data of reports',
            ],
            'Sales Dashboard' => [
                'sales dashboard'   => 'access to live sales dashboard',
            ],
            'Activity Plan/MCP' => [
                'mcp access'        => 'access to activity plan module',
                'mcp create'        => 'access to create activity plan',
                'mcp edit'          => 'access to edit activity plan',
                'mcp delete'        => 'access to delete activity plan',
                'mcp approval'      => 'access to approve activity plan',
                'mcp confirmation'  => 'access to confirm activity plan',
            ],
            'Weekly Activity Report' => [
                'war access'    => 'access to weekly activity report module',
                'war create'    => 'access to create weekly activity report',
                'war edit'      => 'access to edit weekly activity report',
                'war delete'    => 'access to delete weekly activity report',
                'war approve'   => 'access to approve weekly activity report',
            ],
            'Productivity Report' => [
                'productivity report access'    => 'access to productivity report module',
                'productivity report upload'    => 'access to upload productivity report',
            ],
            'Salesman' => [
                'salesman access'   => 'access to salesman module',
                'salesman create'   => 'access to create salesman',
                'salesman edit'     => 'access to edit salesman',
                'salesman delete'   => 'access to delete salesman',
            ],
            'Salesman Location' => [
                'salesmen location access'  => 'access to salesman location module',
                'salesmen location create'  => 'access to create salesman location',
                'salesmen location edit'    => 'access to edit salesman location',
                'salesmen location delete'  => 'access to delete salesman location',
            ],
            'Channel Operation' => [
                'channel operation report'  => 'access to channel operation reports',
                'channel operation print'   => 'access to print channel operation report data',
                'channel operation list'    => 'access to channel operation submission list',
            ],
            'Sales Order Cut-off' => [
                'so cut-off access' => 'access to sales order cut-off module',
                'so cut-off create' => 'access to create sales order cut-off',
                'so cut-off edit'   => 'access to edit sales order cut-off',
                'so cut-off delete' => 'access to delete sales order cut-off',
            ],
            'Company' => [
                'company access'    => 'access to company module',
                'company create'    => 'access to create company',
                'company edit'      => 'access to edit company',
                'company delete'    => 'access tp delete company',
            ],
            'Discount' => [
                'discount access'   => 'access to discount module',
                'discount create'   => 'access to create discount',
                'discount edit'     => 'access to edit discount',
                'discount delete'   => 'access to delete discount',
            ],
            'Account' => [
                'account access'    => 'access to account module',
                'account create'    => 'access to create account',
                'account edit'      => 'access to edit account',
                'account delete'    => 'access to delete account',
            ],
            'Account Reference' => [
                'account reference access'  => 'access to account reference module',
                'account reference create'  => 'access to create account reference',
                'account reference edit'    => 'access to edit account reference',
                'account reference delete'  => 'access to delete account reference',
            ],
            'Shipping Address' => [
                'shipping address access'   => 'access to shipping address module',
                'shipping address create'   => 'access to create shipping address',
                'shipping address edit'     => 'access to edit shipping address',
                'shipping address delete'   => 'access to delete shipping address',
            ],
            'Branches' => [
                'branch access' => 'access to branches module',
                'branch create' => 'access to create branches',
                'branch edit'   => 'access to edit branches',
                'branch delete' => 'access to delete branches',
            ],
            'Branch Address' => [
                'branch address access' => 'access to branch address module',
                'branch address create' => 'access to create branch address',
                'branch address edit'   => 'access to edit branch address',
                'branch address upload' => 'access to upload branch address',
                'branch address delete' => 'access to delete branch address',
            ],
            'Regions' => [
                'region access' => 'access to regions module',
                'region create' => 'access to create regions',
                'region edit'   => 'access to edit regions',
                'region delete' => 'access to delete regions',
            ],
            'Classification' => [
                'classification access' => 'access to classification module',
                'classification create' => 'access to create classification',
                'classification edit'   => 'access to edit classification',
                'classification delete' => 'access to delete classification',
            ],
            'Area' => [
                'area access'   => 'access to area module',
                'area create'   => 'access to create area',
                'area edit'     => 'access to edit area',
                'area delete'   => 'access to delete area',
            ],
            'Invoice Terms' => [
                'invoice term access'   => 'access to invoice terms module',
                'invoice term create'   => 'access to create invoice terms',
                'invoice term edit'     => 'access to edit invoice terms',
                'invoice term delete'   => 'access to delete invoice terms',
            ],
            'Products' => [
                'product access'    => 'access to products module',
                'product create'    => 'access to create products',
                'product edit'      => 'access to edit products',
                'product delete'    => 'access to delete products',
            ],
            'Price Code' => [
                'price code access' => 'access to price code module',
                'price code create' => 'access to create price code',
                'price code edit'   => 'access to edit price code',
                'price code delete' => 'access to delete price code',
            ],
            'Sales People' => [
                'sales people access'   => 'access to sales people module',
                'sales person create'   => 'access to create sales person',
                'sales person edit'     => 'access to edit sales person',
                'sales person delete'   => 'access to delete sales person',
            ],
            'Operation Process' => [
                'operation process access'  => 'access to operation process module',
                'operation process create'  => 'access to create operation process',
                'operation process edit'    => 'access to edit operation process',
                'operation process delete'  => 'access to delete operation process',
            ],
            'Cost Center' => [
                'cost center access'    => 'access to cost center module',
                'cost center create'    => 'access to create cost center',
                'cost center edit'      => 'access to edit cost center',
                'cost center delete'    => 'access to delete cost center',
            ],
            'District' => [
                'district access'   => 'access to district module',
                'district create'   => 'access to create district',
                'district edit'     => 'access to edit district',
                'district delete'   => 'access to delete district',
                'district assign'   => 'assign districts',
            ],
            'Territory' => [
                'territory access'  => 'access to territory module',
                'territory create'  => 'access to create territory',
                'territory edit'    => 'access to edit territory',
                'territory delete'  => 'access to delete territory',
                'territory assign'  => 'assign territories',
            ],
            'Holiday' => [
                'holiday access'    => 'access to holiday module',
                'holiday create'    => 'access to create holiday',
                'holiday edit'      => 'access to edit holiday',
                'holiday delete'    => 'access to delete holiday',
            ],
            'Account Logins' => [
                'account login access' => 'access to account logins',
                'account login export' => 'export account logins',
            ],
            'Users' => [
                'user access'           => 'access to users',
                'user create'           => 'create users',
                'user upload'           => 'upload users',
                'user edit'             => 'edit users',
                'user change password'  => 'change user passwords',
                'user delete'           => 'delete users',
            ],
            'Organizational Structure' => [
                'organizational structure access'   => 'access to organizational structure',
                'organizational structure create'   => 'create organizational structure',
                'organizational structure edit'     => 'edit organizational structure',
                'organizational structure delete'   => 'delete organizational structure',
            ],
            'Roles' => [
                'role access'   => 'access to roles',
                'role create'   => 'create roles',
                'role edit'     => 'edit roles',
                'role delete'   => 'delete roles',
            ],
            'System Log' => [
                'system logs' => 'system logs',
            ],
            'Setting' => [
                'settings' => 'settings access',
            ],
            'Trip' => [
                'trip access'           => 'access to trip module',
                'trip print'            => 'access to print trip detail',
                'trip create'           => 'access to create trip',
                'trip edit'             => 'access to edit trip details.',
                'trip finance approver' => 'access to approve or reject trip ticket requests',
                'trip attachment'       => 'access to attach files to trip request.',
                'trip invoice'          => 'access to add invoice and supplier details to trip request.',
            ],
            'Department' => [
                'department access' => 'Access to departments module',
                'department create' => 'Access to create a department.',
                'department edit'   => 'Access to edit department details',
                'department delete' => 'Access to delete department',
            ],
            'Ship Address Mapping' => [
                'ship address mapping access'   => 'access to ship address mapping',
                'ship address mapping create'   => 'access to create ship address mapping',
                'ship address mapping edit'     => 'access to edit ship address mapping',
                'ship address mapping delete'   => 'access to delete ship address mapping',
            ],
            'Paf' => [
                'paf access'    => 'access to paf module',
                'paf create'    => 'access to create paf',
                'paf edit'      => 'access to edit paf',
                'paf delete'    => 'access to delete paf',
            ],
            'Brand' => [
                'brand access'  => 'access to brand module',
                'brand create'  => 'access to create brand',
                'brand edit'    => 'access to edit brand',
                'brand delete'  => 'access to delete brand',
            ],
            'Pre Plan' => [
                'pre plan access'   => 'access to pre plan module',
                'pre plan create'   => 'access to create pre plan',
                'pre plan edit'     => 'access to edit pre plan',
                'pre plan delete'   => 'access to delete pre plan',
            ],
            'Paf Activity' => [
                'paf activity access'   => 'access to paf activity module',
                'paf activity create'   => 'access to create paf activity',
                'paf activity edit'     => 'access to edit paf activity',
                'paf activity delete'   => 'access to delete paf activity',
            ],
            'Remittances' => [
                'remittance access' => 'access remittance module.'
            ],
            'Upload Templates' => [
                'upload template access'    => 'access to upload templates',
                'upload template create'    => 'access to create upload templates',
                'upload template edit'      => 'access to edit upload templates',
                'upload template delete'    => 'access to delete upload templates',
            ]
        ];

        foreach($permissions_arr as $module => $permissions) {
            foreach($permissions as $permission => $description) {
                Permission::create([
                    'name' => $permission,
                    'module' => $module,
                    'description' => $description,
                ]);
            }
        }
    }
}

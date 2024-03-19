<div>
    <div class="card card-primary card-tabs">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{$tab == 'detail' ? 'active' : ''}}" id="tab-detail-tab" data-toggle="pill" href="#tab-detail" role="tab" aria-controls="tab-detail" aria-selected="{{$tab == 'detail' ? 'true' : 'false'}}" wire:click="selectTab('detail')">
                        Department Detail
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$tab == 'structure' ? 'active' : ''}}" id="tab-structure-tab" data-toggle="pill" href="#tab-structure" role="tab" aria-controls="tab-structure" aria-selected="{{$tab == 'structure' ? 'true' : 'false'}}" wire:click="selectTab('structure')">
                        Department Structure
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#custom-tabs-five-normal" role="tab" aria-controls="custom-tabs-five-normal" aria-selected="false">
                        Normal Tab
                    </a>
                </li> --}}
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="custom-tabs-five-tabContent">
                <div class="tab-pane fade {{$tab == 'detail' ? 'show active' : ''}}" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-tab">
                
                    <strong>
                        <i class="fas fa-code mr-1"></i>
                        Department Code
                    </strong>
                    <p class="text-muted">
                        {{$department->department_code}}
                    </p>
    
                    <hr>
    
                    <strong>
                        <i class="fas fa-signature mr-1"></i>
                        Department Name
                    </strong>
                    <p class="text-muted">
                        {{$department->department_name}}
                    </p>
    
                    <hr>
    
                    <strong>
                        <i class="fas fa-user-shield mr-1"></i>
                        Department Head
                    </strong>
                    <p class="text-muted">
                        {{$department->department_head->fullName() ?? '-'}}
                    </p>
            
                    <hr>
            
                    <strong>
                        <i class="fa fa-user-lock mr-1"></i>
                        Department Admin
                    </strong>
                    <p class="text-muted">
                        {{$department->department_admin->fullName() ?? '-'}}
                    </p>
                    
                </div>
                <div class="tab-pane fade {{$tab == 'structure' ? 'show active' : ''}}" id="tab-structure" role="tabpanel" aria-labelledby="tab-structure-tab">

                    <div class="row">
                        <div class="col-lg-12">
                            <figure class="highcharts-figure">
                                <div id="container"></div>
                                <p class="highcharts-description">
                                </p>
                            </figure>
                        </div>

                        <div class="col-lg-6">
                            @if($form_type == 'edit')
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button class="btn btn-secondary" type="button" wire:click="cancel">
                                        <i class="fa fa-ban mr-1"></i>
                                        CANCEL
                                    </button>
                                </div>
                            </div>
                            @endif

                            <form wire:submit.prevent="submitStructure">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="user">User</label>
                                            <select id="user" class="form-control{{$errors->has('user_select') ? ' is-invalid' : ''}}" wire:model="user_select">
                                                <option value="">- Select user -</option>
                                                @foreach($users as $user)
                                                    <option value="{{$user->id}}">{{$user->fullName()}}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-danger">{{$errors->first('user_select')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="designation">Designation</label>
                                            <input type="text" class="form-control{{$errors->has('designation') ? ' is-invalid' : ''}}" id="designation" wire:model="designation">
                                            <p class="text-danger">{{$errors->first('designation')}}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="reports_to">Reports to</label>
                                            <select id="reports_to" class="form-control{{$errors->has('reports_to_ids') ? ' is-invalid' : ''}}" multiple wire:model="reports_to_ids">
                                                <option value="NULL">- Select user -</option>
                                                @foreach($structures_option as $option)
                                                    <option value="{{$option->id}}">{{$option->user->fullName()}} - {{$option->designation}}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-danger">{{$errors->first('reports_to_ids')}}</p>
                                        </div>
                                    </div>

                                    @if($form_type == 'add')
                                        <div class="col-12 text-right">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fa fa-plus mr-1"></i>
                                                ADD STRUCTURE
                                            </button>
                                        </div>
                                    @else
                                        <div class="col-12 text-right">
                                            <button class="btn btn-success" type="submit">
                                                <i class="fa fa-pencil-alt mr-1"></i>
                                                UPDATE STRUCTURE
                                            </button>
                                        </div>
                                    @endif

                                </div>
                            </form>
                            
                            <hr>

                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">STRUCTURE</h3>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Designation</th>
                                                <th>Reports to</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($structures->count() > 0)
                                                @foreach($structures as $structure)
                                                    <tr>
                                                        <td>{{$structure->user->fullName()}}</td>
                                                        <td>{{$structure->designation}}</td>
                                                        <td>
                                                            @php
                                                                $user_arr = array();
                                                                if(!empty($structure->reports_to_ids)) {
                                                                    $reports_to_ids = explode(',', $structure->reports_to_ids);
                                                                    foreach($reports_to_ids as $structure_id) {
                                                                        $structure_report = \App\Models\DepartmentStructure::find($structure_id);
                                                                        $user = \App\Models\User::find($structure_report->user_id);
                                                                        if(!empty($user)) {
                                                                            $user_arr[] = $user;
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            @if(!empty($user_arr))
                                                                @foreach($user_arr as $user)
                                                                    <p class="mb-0">-{{$user->fullName()}}</p>
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                        <td class="text-center p-0">
                                                            <a href="" class="btn btn-success btn-xs" wire:click.prevent="editStructure({{$structure->id}})">
                                                                <i class="fa fa-pencil-alt"></i>
                                                            </a>
                                                            <a href="" class="btn btn-danger btn-xs">
                                                                <i class="fa fa-trash-alt"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center">
                                                        - no data available -
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    {{$structures->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
    
                </div>
                <div class="tab-pane fade" id="custom-tabs-five-normal" role="tabpanel" aria-labelledby="custom-tabs-five-normal-tab">
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            Highcharts.chart('container', {
                chart: {
                    height: 700,
                    inverted: true
                },
    
                title: {
                    text: 'Highcharts Org Chart'
                },
    
                accessibility: {
                    point: {
                        descriptionFormat: '{add index 1}. {toNode.name}' +
                            '{#if (ne toNode.name toNode.id)}, {toNode.id}{/if}, ' +
                            'reports to {fromNode.id}'
                    }
                },
    
                series: [{
                    type: 'organization',
                    name: 'Highsoft',
                    keys: ['from', 'to'],
                    data: @php echo json_encode($chart_data); @endphp,
                    levels: [{
                        level: 0,
                        color: 'silver',
                        dataLabels: {
                            color: 'black'
                        },
                        height: 25
                    }, {
                        level: 1,
                        color: 'silver',
                        dataLabels: {
                            color: 'black'
                        },
                        height: 25
                    }, {
                        level: 2,
                        color: '#980104'
                    }, {
                        level: 4,
                        color: '#359154'
                    }],
                    nodes: @php echo json_encode($nodes); @endphp,
                    colorByPoint: false,
                    color: '#007ad0',
                    dataLabels: {
                        color: 'white'
                    },
                    borderColor: 'white',
                    nodeWidth: 100
                }],
                tooltip: {
                    outside: true
                },
                exporting: {
                    allowHTML: true,
                    sourceWidth: 800,
                    sourceHeight: 600
                }
    
            });

            window.addEventListener('load-chart', e => {
                Highcharts.chart('container', {
                    chart: {
                        height: 700,
                        inverted: true
                    },
        
                    title: {
                        text: 'Highcharts Org Chart'
                    },
        
                    accessibility: {
                        point: {
                            descriptionFormat: '{add index 1}. {toNode.name}' +
                                '{#if (ne toNode.name toNode.id)}, {toNode.id}{/if}, ' +
                                'reports to {fromNode.id}'
                        }
                    },
        
                    series: [{
                        type: 'organization',
                        name: 'Highsoft',
                        keys: ['from', 'to'],
                        data: e.detail.data,
                        levels: [{
                            level: 0,
                            color: 'silver',
                            dataLabels: {
                                color: 'black'
                            },
                            height: 25
                        }, {
                            level: 1,
                            color: 'silver',
                            dataLabels: {
                                color: 'black'
                            },
                            height: 25
                        }, {
                            level: 2,
                            color: '#980104'
                        }, {
                            level: 4,
                            color: '#359154'
                        }],
                        nodes: e.detail.nodes,
                        colorByPoint: false,
                        color: '#007ad0',
                        dataLabels: {
                            color: 'white'
                        },
                        borderColor: 'white',
                        nodeWidth: 100
                    }],
                    tooltip: {
                        outside: true
                    },
                    exporting: {
                        allowHTML: true,
                        sourceWidth: 800,
                        sourceHeight: 600
                    }
        
                });
            });
        });
    </script>
</div>

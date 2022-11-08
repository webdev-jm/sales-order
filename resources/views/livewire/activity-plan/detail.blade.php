<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Plan Details</h3>
            <div class="card-tools text-sm" wire:loading>
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>
        <div class="card-body p-1 table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="text-center text-uppercase">
                        <th>Day</th>
                        <th>Date</th>
                        <th>Exact Location</th>
                        <th>Account</th>
                        <th>Purpose/Activity</th>
                        <th>Work With</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lines as $date => $line)
                        <tr class="text-center {{$line['class']}}">
                            <td class="align-middle text-uppercase font-weight-bold" rowspan="{{count($line['lines']) + 1}}">
                                {{$line['day']}}
                            </td>
                            <td class="align-middle" rowspan="{{count($line['lines']) + 1}}">
                                {{$line['date']}}
                            </td>
                        </tr>
                        @foreach($line['lines'] as $row)
                        <tr class=" {{$line['class']}}">
                            {{-- location --}}
                            <td class="p-0 align-middle">
                                <textarea class="form-control border-0 {{$line['class']}}"></textarea>
                            </td>
                            {{-- branches --}}
                            <td class="p-0">
                                <input type="text" class="form-control border-0 {{$line['class']}}"/>
                            </td>
                            {{-- purpose/activity --}}
                            <td class="p-0 align-middle">
                                <textarea class="form-control border-0 {{$line['class']}}"></textarea>
                            </td>
                            {{-- work with --}}
                            <td class="p-0">
                                <select class="form-control border-0 {{$line['class']}}">
                                    <option value=""></option>
                                    @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->fullName()}}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-light">
                            <td class="text-right px-2" colspan="6">
                                <button class="btn btn-xs btn-info" wire:click.prevent="addLine('{{$date}}')"><i class="fa fa-plus mr-1"></i>Add Line</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function() {
            
        });
    </script>
</div>

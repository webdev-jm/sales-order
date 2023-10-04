<div>
    <table class="table table-sm mb-3 shadow-sm border-3 border-blur-sm border-gray-200 bg-gradient-to-t from-gray-300 via-white to-gray-300">
        <thead>
            <tr class="text-center">
                <th class="text-lg border-0" colspan="2">TOP FAST MOVING SKUs</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($api_data))
                @foreach($api_data as $data)
                <tr>
                    <td class="border-0">{{$data->description ?? $data['description']}}</td>
                    <td class="border-0 font-[900]">{{$data->percent ?? $data['percent']}}%</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

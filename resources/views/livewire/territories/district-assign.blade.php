<div>
    <div class="form-group">
        {!! Form::label('district_id', 'District Code') !!}
        <select name="district_id" id="district_id" class="form-control" form="save_territory" wire:model="district_id">
            
            @foreach ($options as $key => $option)
            <option value="{{$key}}">{{$option}}</option>
            @endforeach
        </select>
    </div>
</div>

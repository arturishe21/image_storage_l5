<div class="tab-content padding-5">
    <label class="label" for="{{$fieldName}}">{{$field['caption']}}</label>
    <div class="imgInfoBox-relative-block">
        <label class="input">
            @if($field['type'] == 'text')
                <input type="text" value="{{$entity->$fieldName}}" name="{{$fieldName}}" placeholder="{{$field['placeholder']}}" class="dblclick-edit-input form-control input-sm unselectable">
            @elseif($field['type'] == 'textarea')
                <textarea rows="{{$field['rows'] or '3'}}"  id="{{$fieldName}}"  name="{{ $fieldName }}" class="custom-scroll imgInfoBox-textarea">{{$entity->$fieldName}}</textarea>
            @endif
        </label>
    </div>
</div>

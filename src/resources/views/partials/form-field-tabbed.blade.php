<ul class="nav nav-tabs tabs-pull-right">
    <label class="label pull-left">{{__cms($field['caption'])}}</label>
    @foreach($field['tabs'] as $lang => $tab)
        <li class="{{$loop->first ? 'active' : ''}}">
            <a style="color: #000000 !important;" href="#e-{{$fieldName}}-{{$tab['postfix']}}-{{$loop->index}}" data-toggle="tab">{{$tab['caption']}}</a>
        </li>
    @endforeach
</ul>

<div class="tab-content padding-5">
    @foreach($field['tabs'] as $lang => $tab)
        <div class="tab-pane {{$loop->first ? 'active' : ''}}" id="e-{{$fieldName}}-{{$tab['postfix']}}-{{$loop->index}}">
            <div class="imgInfoBox-relative-block">
                <? $fieldName = $fieldName.$tab['postfix'] ?>
                <label class="input">
                    @if($field['type'] == 'text')
                        <input type="text" value="{{$entity->$fieldName}}" name="{{$fieldName}}" placeholder="{{$tab['placeholder']}}" class="dblclick-edit-input form-control input-sm unselectable">
                    @elseif($field['type'] == 'textarea')
                        <textarea rows="{{$field['rows'] or '3'}}" id="{{$fieldName}}"  name="{{ $fieldName }}" class="custom-scroll imgInfoBox-textarea">{{$entity->$fieldName}}</textarea>
                    @endif
                </label>
            </div>
        </div>
    @endforeach
</div>
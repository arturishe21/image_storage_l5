<tr class="tr_{{$el->id}} " id_page="{{$el->id}}">
    <td>
        {{$el->id}}
    </td>
    <td>
        {{$el->title}}
    </td>
    <td colspan="2">{{$el->created_at}}</td>
    <td>
            <span>
                @if ($el->is_active)
                    <span class="glyphicon glyphicon-ok"></span>
                @else
                    <span class="glyphicon glyphicon-minus"></span>
                @endif
            </span>
    </td>
    <td>
        <div class="btn-group hidden-phone pull-right">
            <a class="btn dropdown-toggle btn-xs btn-default"  data-toggle="dropdown"><i class="fa fa-cog"></i> <i class="fa fa-caret-down"></i></a>
            <ul class="dropdown-menu pull-right" id_rec ="{{$el->id}}">
                <li>
                    <a class="edit_record" onclick="ImageStorage.getTagEditForm({{$el->id}})"><i class="fa fa-pencil"></i> {{__cms('Редактировать')}}</a>
                </li>
                <li>
                    <a onclick="ImageStorage.deleteTag({{$el->id}});"><i class="fa red fa-times"></i> {{__cms("Удалить")}}</a>
                </li>
            </ul>
        </div>
    </td>
</tr>
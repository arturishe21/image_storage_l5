<table class="table  table-hover table-bordered " id="sort_t">
    <thead>
    <tr>
        <th width="25%">{{__cms("Название")}}</th>
        <th width="10%">{{__cms("Создана (от)")}}</th>
        <th width="10%">{{__cms("Создана (до)")}}</th>
        <th width="10%">{{__cms("Связанные галереи")}}</th>
        <th width="10%">{{__cms("Связанные теги")}}</th>
        <th width="1%">
            <form class="smart-form pull-right" id="upload-image-storage-form">
                <div class="input input-file image-storage-images">
                    <span class="button">
                        <input type="file" name="images[]" multiple="multiple" accept="image/*" onchange="ImageStorage.uploadImage(this);">
                        {{__cms("Выбрать")}}
                    </span>
                    <input type="text" class="j-image-title" placeholder="{{__cms("Загрузить изображения")}}">
                </div>
            </form>
        </th>
    </tr>
    <form id="image-storage-search-form">
    <tr class="image-storage-filters-row">
        <td>
            <div class="relative">
                <input type="text" value="" name="image_storage_filter[filterByTitle]" class="form-control input-small">
            </div>
        </td>
        <td>
            <div>
                <input type="text"
                       id="f-datepicker-from"
                       value=""
                       name="image_storage_filter[filterByDate][from]"
                       class="form-control input-small datepicker" >
                <span class="input-group-addon form-input-icon form-input-filter-icon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
        </td>
        <td>
            <div>
                <input type="text"
                       id="f-datepicker-to"
                       value=""
                       name="image_storage_filter[filterByDate][to]"
                       class="form-control input-small datepicker" >
                <span class="input-group-addon form-input-icon form-input-filter-icon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
        </td>
        <td>
            <select name="image_storage_filter[filterByGalleries][]" multiple="multiple" class="image-storage-select">
                @foreach($galleries as $gallery)
                    <option value="{{$gallery->id}}">{{$gallery->title}}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="image_storage_filter[filterByTags][]" multiple="multiple" class="image-storage-select">
                @foreach($tags as $tag)
                    <option value="{{$tag->id}}">{{$tag->title}}</option>
                @endforeach
            </select>
        </td>
        <td>
            <button class="btn btn-default btn-sm image-storage-button"
                    type="button"
                    onclick="ImageStorage.doSearch();">
                {{__cms('Поиск') }}
            </button>
            <button class="btn btn-default btn-sm image-storage-button"
                    type="button"
                    onclick="ImageStorage.doResetFilters();">
                {{__cms('Сбросить') }}
            </button>
        </td>
    </tr>
    </form>
    </thead>
</table>
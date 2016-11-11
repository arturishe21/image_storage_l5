<div class="image-storage-image-operations well">
    <form name="image-storage-image-operations-form">
    <div class="col-md-12 image-storage-image-operations-row">
        <div class="col-md-2">{{__cms('Создать новую галерею')}}</div>
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" name="gallery_name" class="form-control image-storage-image-operations-input" placeholder="{{__cms('Название галереи')}}">
            </div>
        </div>
        <div class="col-md-2">
            <a onclick="ImageStorage.createGalleryWithImages(this);" href="javascript:void(0);" class="btn btn-default btn-sm image-storage-image-operations-button">{{__cms('Создать')}}</a>
        </div>
    </div>
    <div class="col-md-12 image-storage-image-operations-row">
        <div class="col-md-2">{{__cms('Добавить в галереи')}}</div>
        <div class="col-md-8">
            <div class="input-group">
                <select name="relations[image-storage-galleries][]" class="image-storage-image-operations-input image-storage-select" multiple="multiple">
                    @foreach($galleries as $gallery)
                        <option value="{{ $gallery->id }}">{{ $gallery->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <!--fixme сделать  -->
            <a onclick="ImageStorage.saveImagesGalleriesRelations();" href="javascript:void(0);" class="btn btn-default btn-sm image-storage-image-operations-button">{{__cms('Сохранить')}}</a>
        </div>
    </div>
    <div class="col-md-12 image-storage-image-operations-row">
        <div class="col-md-2">{{__cms('Добавить к тегам')}}</div>
        <div class="col-md-8">
            <div class="input-group">
                <select name="relations[image-storage-tags][]" multiple="multiple" class="image-storage-image-operations-input image-storage-select">
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <!--fixme сделать  -->
            <a onclick="ImageStorage.saveImagesTagsRelations();" href="javascript:void(0);" class="btn btn-default btn-sm image-storage-image-operations-button">{{__cms('Сохранить')}}</a>
        </div>
    </div>
    </form>
</div>


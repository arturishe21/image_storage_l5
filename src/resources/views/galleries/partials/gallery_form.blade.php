<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times; </button>
    <h4 class="modal-title" id="modal_form_label"># {{$entity->id}}: {{$entity->title}} ({{$entity->created_at}}) </h4>
</div>
<div class="modal-body row">
    <input type="hidden" name="gallery_id" value="{{$entity->id}}">
    <div class="tb-uploaded-image-container col-xs-8">
            <ul class="dop_foto image-storage-sortable">
                @forelse ($entity->images as $key => $image)
                    <li id="{{$image->id}}">
                        @include('image-storage::images.partials.single_image')
                        <div class="tb-btn-delete-wrap">
                            <!-- fixme inline styles -->
                            <button class="btn2 btn-default btn-sm tb-btn-image-delete" style="height:22px;"
                                    type="button"
                                    onclick="ImageStorage.deleteGalleryImageRelation({{ $image->id }},{{ $entity->id }});">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </li>
                @empty
                    <div class="no_photo" style="text-align: center; ">
                        {{__cms('Нет изображений')}}
                    </div>
                 @endforelse
            </ul>
    </div>

    <div class="imgInfoBox-container col-xs-4">
            <form class="smart-form" id="imgInfoBox-form-gallery">
                <fieldset>
                    <div class="imgInfoBox-container-content tab-content padding-10">
                        @foreach ($fields as $fieldName => $field)
                            <section>
                                <div class="tab-pane active">
                                    @if(isset($field['tabs']))
                                            <!-- fixme  исправить вьюхи для инпутов-->
                                    @include('image-storage::partials.form-field-tabbed')
                                    @else
                                        @include('image-storage::partials.form-field')
                                    @endif
                                </div>
                            </section>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                 <section><label>{{__cms('Теги')}}</label>
                        <select name="relations[image-storage-tags][]" multiple class="imgInfoBox-select image-storage-select">
                            @foreach ($tags as $tag)
                                <option {{$entity->tags->contains($tag->id) ? 'selected="selected"' : ''}} value="{{$tag->id}}">{{$tag->title}}</option>
                            @endforeach
                        </select>
                    </section>
                </fieldset>

                <div class="well action-buttons-row">
                    <a onclick="ImageStorage.saveGalleryInfo({{ $entity->id }});" href="javascript:void(0);"
                       class="btn btn-success btn-sm pull-right j-btn-save">{{__cms('Сохранить')}}</a>
                    <a onclick="ImageStorage.deleteGallery({{ $entity->id }});" href="javascript:void(0);"
                       class="btn btn-danger btn-sm pull-left j-btn-del">{{__cms('Удалить')}}</a>
                </div>
            </form>
    </div>
</div>
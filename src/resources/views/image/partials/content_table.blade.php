<div class="superbox image-storage-container images-container col-sm-12">
    @foreach ($data as $image)
        @include('image-storage::image.partials.list_image')
    @endforeach
</div>
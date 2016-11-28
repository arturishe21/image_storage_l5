<div class="superbox image-storage-container images-container col-sm-12">
    @foreach ($data as $entity)
        @include('image-storage::video.partials.single_list')
    @endforeach
</div>
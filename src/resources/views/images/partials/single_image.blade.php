<img class="superbox-img image-storage-img"
     src="{{ $image->getSource("cms_preview")}}"
     data-id="{{ $image->id }}"
     data-source="{{ $image->getSource() }}"
     data-createdat="{{ $image->created_at }}"
     title="{{ $image->title }}">

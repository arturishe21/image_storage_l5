<img class="superbox-img image-storage-img"
     src="{{ asset($entity->getSource("cms_preview"))}}"
     data-id="{{ $entity->id }}"
     data-source="{{ asset($entity->getSource()) }}"
     data-createdat="{{ $entity->created_at }}"
     title="{{ $entity->title }}">

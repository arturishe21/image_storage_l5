<form id="image-storage-search-form">
    <table class="table  table-hover table-bordered " id="sort_t">
        @include('image-storage::tag.partials.filters_table')
        <tbody>
        @forelse($data as $k=>$el)
            @include('image-storage::tag.partials.row_table')
        @empty
        <tr>
            <td colspan="5"  class="text-align-center">
                {{__cms('Пусто')}}
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</form>
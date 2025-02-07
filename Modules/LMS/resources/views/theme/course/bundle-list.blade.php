@if (count($bundles) > 0)
    <div class="grid grid-cols-12 gap-5 mt-10">
        @foreach ($bundles as $bundle)
            <x-theme::cards.bundle-card-one :bundle="$bundle" />
        @endforeach
    </div>
@else
    <x-theme::cards.empty btn="true" btntext="Go Back" btnAction="{{ route('course.bundle') }}"
        title="No Bundle Found" />
@endif
{!! $bundles->links('theme::pagination.pagination-one') !!}

<x-dashboard-layout>
    <x-slot:title>{{ isset($bundle) ? translate('Edit') : translate('Create') }} {{ translate('Bundle') }} </x-slot:title>
    <!-- BREADCRUMB -->
    <x-portal::admin.breadcrumb back-url="{{ route('instructor.bundle.index') }}"
        title="{{ isset($bundle) ? 'Edit' : 'Create' }} Bundle" page-to="Bundle" />
    <!-- Start Course Bundle Form -->
    @php
        $bundle = $bundle ?? null;
    @endphp
    <x-portal::course.bundle.create-form :bundle=$bundle />
</x-dashboard-layout>

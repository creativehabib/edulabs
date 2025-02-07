@php
    $settings = [
        
    ];
@endphp

@include('theme::layouts.partials.head')

<body class="home-online-education">
    @include('theme::layouts.partials.header', [
        'style' => 'one',
        'class' =>"flex-center bg-white lg:bg-header shadow-md py-4 fixed inset-0 h-[theme('spacing.header')] z-[101]",
        'data' => [
            'header_class' => "flex-center bg-white lg:bg-header shadow-md py-4 fixed inset-0 h-[theme('spacing.header')] z-[101]",
            'components' => [
                'inner-header-top' => '',
            ],
        ]
    ])
    <main>
        {{ $slot }}
    </main>

    @include('theme::layouts.partials.footer-script', ['data' => []])
</body>

</html>

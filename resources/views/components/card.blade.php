@props(['padding' => 'p-5 sm:p-7'])

<section {{ $attributes->class(['bookplate rounded-3xl', $padding]) }}>
    {{ $slot }}
</section>

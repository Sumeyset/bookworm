@props(['padding' => 'p-5 sm:p-7'])

<section {{ $attributes->class(['bookplate rounded-xl', $padding]) }}>
    {{ $slot }}
</section>

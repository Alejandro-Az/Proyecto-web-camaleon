@php
    /** @var \App\Models\Event $event */
    /** @var \Illuminate\Support\Collection|\App\Models\EventRomanticPhrase[] $phrases */

    $random = (bool) data_get($event->settings, 'romantic_phrases_random', true);
    $limit  = (int) data_get($event->settings, 'romantic_phrases_limit', 8);
    $title  = data_get($event->settings, 'romantic_phrases_title', 'Frases del evento');
    $subtitle = data_get($event->settings, 'romantic_phrases_subtitle', 'Un poquito de amor para ir calentando motores…');

    $list = $phrases;
    if ($random) {
        $list = $list->shuffle();
    }
    if ($limit > 0) {
        $list = $list->take($limit);
    }
@endphp

<section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow">
    <div class="mb-4">
        <h2 class="text-xl font-semibold">{{ $title }}</h2>
        <p class="text-sm text-slate-300">{{ $subtitle }}</p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @foreach($list as $row)
            <figure class="rounded-2xl bg-slate-900/60 border border-slate-700 p-4">
                <blockquote class="text-slate-100 text-sm leading-relaxed">
                    “{{ $row->phrase }}”
                </blockquote>
                @if($row->author)
                    <figcaption class="text-xs text-slate-400 mt-2">— {{ $row->author }}</figcaption>
                @endif
            </figure>
        @endforeach
    </div>
</section>

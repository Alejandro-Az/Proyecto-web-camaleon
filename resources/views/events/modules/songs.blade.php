<div style="padding: 80px 24px; ">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;"><div class="cml-eyebrow" style="margin-bottom: 14px;">Música</div><h2 class="cml-serif text-4xl italic text-[var(--ink)]">Pide tu canción favorita</h2><div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div></div>

    @php
        $playlistEnabled        = data_get($event->settings, 'playlist_enabled', true);
        $allowGuestAddSongs     = data_get($event->settings, 'playlist_allow_guests_to_add_songs', true);
        $maxSongsPerGuest       = data_get($event->settings, 'playlist_max_songs_per_guest');
        $maxVotesPerGuest       = data_get($event->settings, 'playlist_max_votes_per_guest');
        $showSongAuthorGlobally = data_get($event->settings, 'public_show_song_author', true);
        $guestInvitationCode    = $guest->invitation_code ?? null;
    @endphp

    {{-- Contenedor de mensajes (flash + AJAX) --}}
    <div data-song-alerts class="space-y-2">
        @if(session('song_success'))
            <div class="rounded-2xl bg-emerald-500/10 border border-emerald-500/60 px-4 py-3 text-sm text-emerald-100">
                {{ session('song_success') }}
            </div>
        @endif

        @if(session('song_error'))
            <div class="rounded-2xl bg-red-500/10 border border-red-500/60 px-4 py-3 text-sm text-red-100">
                {{ session('song_error') }}
            </div>
        @endif

        @if(session('song_vote_success'))
            <div class="rounded-2xl bg-sky-500/10 border border-sky-500/60 px-4 py-3 text-sm text-sky-100">
                {{ session('song_vote_success') }}
            </div>
        @endif

        @if(session('song_vote_error'))
            <div class="rounded-2xl bg-red-500/10 border border-red-500/60 px-4 py-3 text-sm text-red-100">
                {{ session('song_vote_error') }}
            </div>
        @endif
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            
            <p class="text-sm text-[var(--ink-soft)]">
                Sugiera canciones para la fiesta y vote por sus favoritas.
            </p>
        </div>
        @if($guest && (!is_null($guestSongSuggestionsCount) || !is_null($guestVotesCount)))
            <div class="text-right text-xs text-[var(--ink-soft)]">
                @if(!is_null($guestSongSuggestionsCount))
                    <p>
                        Canciones sugeridas: <span class="font-semibold">{{ $guestSongSuggestionsCount }}</span>
                        @if($maxSongsPerGuest)
                            / {{ $maxSongsPerGuest }}
                        @endif
                    </p>
                @endif
                @if(!is_null($guestVotesCount))
                    <p>
                        Votos usados: <span class="font-semibold">{{ $guestVotesCount }}</span>
                        @if($maxVotesPerGuest)
                            / {{ $maxVotesPerGuest }}
                        @endif
                    </p>
                @endif
            </div>
        @endif
    </div>

    @if(! $playlistEnabled)
        <p class="text-sm text-[var(--ink-soft)]">
            La lista de reproducción no está habilitada para este evento.
        </p>
    @else
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Formulario de sugerencia --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Sugerir una canción</h3>

                @if(! $guest)
                    <p class="text-sm text-[var(--ink-soft)]">
                        Para sugerir canciones, utilice su enlace personalizado de invitación
                        (el que incluye su código único).
                    </p>
                @elseif(! $allowGuestAddSongs)
                    <p class="text-sm text-[var(--ink-soft)]">
                        Por el momento, las sugerencias de canciones están deshabilitadas para este evento.
                    </p>
                @else
                    @if($maxSongsPerGuest)
                        <p class="text-xs text-[var(--ink-muted)]">
                            Puede sugerir hasta {{ $maxSongsPerGuest }} canción(es) con esta invitación.
                        </p>
                    @endif

                    <form method="POST"
                          action="{{ route('events.songs.store', ['slug' => $event->slug]) }}"
                          class="space-y-4"
                          data-song-form="suggestion">
                        @csrf

                        <input type="hidden" name="invitation_code" value="{{ $guestInvitationCode }}">

                        <div>
                            <label for="title" class="block text-sm font-medium mb-1">Título de la canción *</label>
                            <input
                                id="title"
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                class="cml-input"
                                required
                            >
                            @error('title')
                                <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="artist" class="block text-sm font-medium mb-1">Artista / Grupo</label>
                            <input
                                id="artist"
                                type="text"
                                name="artist"
                                value="{{ old('artist') }}"
                                class="cml-input"
                            >
                            @error('artist')
                                <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="url" class="block text-sm font-medium mb-1">
                                Enlace (Spotify, YouTube, etc.)
                            </label>
                            <input
                                id="url"
                                type="url"
                                name="url"
                                value="{{ old('url') }}"
                                class="cml-input"
                                placeholder="https://..."
                            >
                            @error('url')
                                <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="message_for_couple" class="block text-sm font-medium mb-1">
                                Mensaje para los festejados (opcional)
                            </label>
                            <textarea
                                id="message_for_couple"
                                name="message_for_couple"
                                rows="3"
                                class="cml-input"
                            >{{ old('message_for_couple') }}</textarea>
                            @error('message_for_couple')
                                <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-start gap-2">
                            <input
                                id="show_author"
                                type="checkbox"
                                name="show_author"
                                value="1"
                                @checked(old('show_author', true))
                                class="mt-1 rounded border-[var(--rule)] text-[var(--accent)] focus:ring-pink-500"
                            >
                            <label for="show_author" class="text-sm text-[var(--ink)]">
                                Acepto que mi nombre aparezca junto a esta canción.
                            </label>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="cml-btn cml-btn--accent"
                                data-song-submit="suggestion">
                                Enviar sugerencia
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Lista de canciones con votos --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Lista de canciones sugeridas</h3>

                @if($event->songs->isEmpty())
                    <p class="text-sm text-[var(--ink-soft)]" data-song-list-empty>
                        Aún no hay canciones sugeridas. ¡Sea la primera persona en agregar una!
                    </p>
                @endif

                <ul data-song-list style="background: var(--surface); border: 1px solid var(--rule); padding: 4px 0;">
                    @foreach($event->songs as $song)
                        @php
                            $hasVoted = $guest && in_array($song->id, $votedSongIds ?? []);
                        @endphp
                        <li class="flex flex-col gap-2" data-song-item-id="{{ $song->id }}" style="padding: 14px 20px; border-bottom: 1px solid var(--rule);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-[var(--ink)]">
                                        {{ $song->title }}
                                    </p>

                                    @if($song->artist)
                                        <p class="text-xs text-[var(--ink-soft)]">
                                            {{ $song->artist }}
                                        </p>
                                    @endif

                                    @if(!empty($song->message_for_couple))
                                        <p class="text-xs text-[var(--ink-soft)] mt-1 italic">
                                            “{{ $song->message_for_couple }}”
                                        </p>
                                    @endif

                                    @if($showSongAuthorGlobally && $song->show_author && $song->suggestedBy)
                                        <p class="text-xs text-[var(--ink-muted)] mt-1">
                                            Sugerida por <span class="font-semibold">{{ $song->suggestedBy->name }}</span>
                                        </p>
                                    @endif

                                    @if($song->url)
                                        <a
                                            href="{{ $song->url }}"
                                            target="_blank"
                                            class="inline-flex items-center text-[11px] text-sky-300 hover:text-sky-200 mt-1 underline"
                                        >
                                            Escuchar / ver enlace
                                        </a>
                                    @endif
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-[var(--ink-soft)] mb-1" data-song-votes>
                                        {{ $song->votes_count }} {{ $song->votes_count === 1 ? 'voto' : 'votos' }}
                                    </p>

                                    @if(! $guest)
                                        <p class="text-[11px] text-[var(--ink-muted)]">
                                            Use su enlace de invitación<br>para votar.
                                        </p>
                                    @else
                                        <form method="POST"
                                              action="{{ route('events.songs.vote', ['slug' => $event->slug, 'song' => $song->id]) }}"
                                              data-song-form="vote">
                                            @csrf
                                            <input type="hidden" name="invitation_code" value="{{ $guestInvitationCode }}">
                                            <button
                                                type="submit"
                                                class="{{ $hasVoted ? 'cml-btn cml-btn--ghost' : 'cml-btn cml-btn--accent' }}"
                                                style="padding: 8px 14px; font-size: 11px;"
                                                data-song-vote-button>
                                                {{ $hasVoted ? 'Quitar mi voto' : 'Votar por esta canción' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    </div>
</div>

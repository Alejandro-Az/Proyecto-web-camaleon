<section id="playlist"
         class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow space-y-6"
         data-module="songs">
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
            <h2 class="text-xl font-semibold">Canciones que no pueden faltar</h2>
            <p class="text-sm text-slate-300">
                Sugiera canciones para la fiesta y vote por sus favoritas.
            </p>
        </div>
        @if($guest && (!is_null($guestSongSuggestionsCount) || !is_null($guestVotesCount)))
            <div class="text-right text-xs text-slate-300">
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
        <p class="text-sm text-slate-300">
            La lista de reproducción no está habilitada para este evento.
        </p>
    @else
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Formulario de sugerencia --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Sugerir una canción</h3>

                @if(! $guest)
                    <p class="text-sm text-slate-300">
                        Para sugerir canciones, utilice su enlace personalizado de invitación
                        (el que incluye su código único).
                    </p>
                @elseif(! $allowGuestAddSongs)
                    <p class="text-sm text-slate-300">
                        Por el momento, las sugerencias de canciones están deshabilitadas para este evento.
                    </p>
                @else
                    @if($maxSongsPerGuest)
                        <p class="text-xs text-slate-400">
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
                                class="w-full rounded-2xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                                class="w-full rounded-2xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                                class="w-full rounded-2xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                                class="w-full rounded-2xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                                class="mt-1 rounded border-slate-500 text-pink-500 focus:ring-pink-500"
                            >
                            <label for="show_author" class="text-sm text-slate-200">
                                Acepto que mi nombre aparezca junto a esta canción.
                            </label>
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-500 hover:bg-pink-400 text-sm font-semibold shadow"
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
                    <p class="text-sm text-slate-300" data-song-list-empty>
                        Aún no hay canciones sugeridas. ¡Sea la primera persona en agregar una!
                    </p>
                @endif

                <ul class="divide-y divide-slate-700/60" data-song-list>
                    @foreach($event->songs as $song)
                        @php
                            $hasVoted = $guest && in_array($song->id, $votedSongIds ?? []);
                        @endphp
                        <li class="py-3 flex flex-col gap-2" data-song-item-id="{{ $song->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-100">
                                        {{ $song->title }}
                                    </p>

                                    @if($song->artist)
                                        <p class="text-xs text-slate-300">
                                            {{ $song->artist }}
                                        </p>
                                    @endif

                                    @if(!empty($song->message_for_couple))
                                        <p class="text-xs text-slate-300 mt-1 italic">
                                            “{{ $song->message_for_couple }}”
                                        </p>
                                    @endif

                                    @if($showSongAuthorGlobally && $song->show_author && $song->suggestedBy)
                                        <p class="text-xs text-slate-400 mt-1">
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
                                    <p class="text-xs text-slate-300 mb-1" data-song-votes>
                                        {{ $song->votes_count }} {{ $song->votes_count === 1 ? 'voto' : 'votos' }}
                                    </p>

                                    @if(! $guest)
                                        <p class="text-[11px] text-slate-400">
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
                                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-[11px] font-semibold shadow
                                                {{ $hasVoted ? 'bg-slate-700 text-slate-100 hover:bg-slate-600' : 'bg-sky-500 text-white hover:bg-sky-400' }}"
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
</section>

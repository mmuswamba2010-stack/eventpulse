<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('organizer.events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700 mb-2">
            <x-icon name="arrow-left" class="w-4 h-4" /> Mes événements
        </a>
        <h2 class="flex items-center gap-2 font-extrabold text-2xl text-slate-900 tracking-tight">
            <x-icon name="pencil-square" class="w-7 h-7 text-brand" />
            Modifier — {{ $event->title }}
        </h2>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 px-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('organizer.events._form', ['event' => $event])

                    <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('organizer.events.index') }}"
                           class="px-5 py-2.5 text-sm font-semibold text-slate-500 hover:text-slate-700">Annuler</a>
                        <x-primary-button class="py-3 px-6">
                            <x-icon name="check" class="w-4 h-4" /> Enregistrer les modifications
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

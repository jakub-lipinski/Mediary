<x-app-layout>
    <div class="flex flex-wrap gap-6">
        <div
            class="flex flex-col gap-6 w-full bg-white rounded-md border-[1px] border-slate-200 p-6 h-fit lg:w-[calc(50%-12px)]"
        >
            <div class="flex flex-col gap-2">
                <h2 class="text-lg font-medium text-black">
                    Dostosuj nową workoutę
                </h2>
                <span class="text-xs text-gray-400 font-normal"
                    >Dostosuj parametry swojej workouty. Wirtualny asystent
                    bierze pod uwagę również dane z twojego profilu
                    pacjenta.</span
                >
            </div>

            <form
                action="{{ route('workout.store') }}"
                class="w-full flex flex-col gap-6"
                method="post"
            >
                @csrf @METHOD('POST')
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div class="flex flex-col gap-1 text-sm w-full">
                        <label for="workout_name" class="dark:text-white"
                            >Nazwa workouty
                            <span class="text-red-500">*</span></label
                        >
                        <input
                            type="text"
                            name="workout_name"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                            placeholder="Moja nowa workouta"
                            value="{{ old('workout_name') }}"
                        />
                        @error('workout_name')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div
                        class="flex flex-col gap-1 text-sm w-full 2xl:w-[calc(33.333%-12px)]"
                    >
                        <label for="workout_type" class="dark:text-white"
                            >Typ workouty
                            <span class="text-red-500">*</span></label
                        >
                        <select
                            type="text"
                            name="workout_type"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                        >
                            <option value="">Wybierz typ workouty</option>
                            <option value="classic">Klasyczna</option>
                            <option value="wegan">Wegetariańska</option>
                            <option value="keto">Ketogeniczna</option>
                        </select>
                        @error('workout_type')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div
                        class="flex flex-col gap-1 text-sm w-full 2xl:w-[calc(33.333%-12px)]"
                    >
                        <label for="workout_kcal" class="dark:text-white"
                            >Ilość kcal
                            <span class="text-red-500">*</span></label
                        >
                        <select
                            type="number"
                            name="workout_kcal"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                        >
                            <option value="">Wybierz ilość kalorii</option>
                            <option value="1500">1500</option>
                            <option value="2000">2000</option>
                            <option value="2500">2500</option>
                            <option value="3000">3000</option>
                        </select>
                        @error('workout_kcal')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div
                        class="flex flex-col gap-1 text-sm w-full 2xl:w-[calc(33.333%-12px)]"
                    >
                        <label for="meals_count" class="dark:text-white"
                            >Ilość posiłków
                            <span class="text-red-500">*</span></label
                        >
                        <select
                            type="number"
                            name="meals_count"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                        >
                            <option value="">Ilość posiłków</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        @error('meals_count')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div
                        class="flex flex-col gap-1 w-full text-sm lg:w-[calc(50%-8px)]"
                    >
                        <label for="workout_like" class="dark:text-white"
                            >Podaj składniki, które lubisz jeść</label
                        >
                        <textarea
                            type="number"
                            name="workout_like"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 resize-none h-24 text-sm"
                            placeholder="Np. jabłka, ogórki..."
                        ></textarea>
                    </div>
                    <div
                        class="flex flex-col gap-1 w-full text-sm lg:w-[calc(50%-8px)]"
                    >
                        <label for="workout_not_like" class="dark:text-white"
                            >Podaj składniki, których nie lubisz jeść</label
                        >
                        <textarea
                            type="number"
                            name="workout_not_like"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 resize-none h-24 text-sm"
                            placeholder="Np. pomidory, cebula..."
                        ></textarea>
                    </div>
                </div>
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div class="flex flex-col gap-1 w-full text-sm">
                        <label for="workout_other_notes" class="dark:text-white"
                            >Dodatkowe uwagi</label
                        >
                        <textarea
                            type="text"
                            name="workout_other_notes"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 resize-none h-24 text-sm"
                            placeholder="Np. bez gotowanych potraw"
                        ></textarea>
                    </div>
                </div>
                <div class="w-full">
                    <button
                        class="rounded-md bg-blue-600 text-white w-fit px-6 py-2 text-base duration-200 hover:bg-blue-700"
                    >
                        Stwórz
                    </button>
                </div>
            </form>
        </div>

        <div
            class="flex flex-col gap-6 w-full bg-white rounded-md border-[1px] border-slate-200 p-6 h-fit lg:w-[calc(50%-12px)]"
        >
            <div
                class="flex flex-wrap justify-center items-center md:justify-between"
            >
                <div class="flex flex-col gap-2">
                    <h2 class="text-lg font-medium text-black">
                        Twoje workouty
                    </h2>
                    <span class="text-xs text-gray-400 font-normal"
                        >Lista twoich zapisanych workout.</span
                    >
                </div>
                <button
                    class="show-deleted-workouts rounded-md bg-red-100 text-red-500 px-6 py-2 text-sm duration-200 hover:bg-red-200"
                >
                    Usunięte workouty
                </button>
                <button
                    class="show-my-workouts rounded-md bg-blue-100 text-blue-500 px-6 py-2 text-sm duration-200 hover:bg-blue-200 hidden"
                >
                    Moje workouty
                </button>
            </div>

            <div class="flex flex-wrap gap-4">
                @forelse($workouts as $workout)
                <div
                    class="workout w-full border-[1px] border-slate-200 p-4 rounded-md flex flex-col gap-4 shadow-xs"
                >
                    <h2 class="text-lg font-semibold text-blue-600">
                        {{ $workout->workout_name }}
                    </h2>
                    <div class="flex flex-col gap-2">
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-utensils text-blue-600"
                                ></i>
                                Cel:
                            </span>
                            @if($workout->target === 'lose') Schudnięcie
                            @elseif($workout->target === 'gain') Zwiększenie
                            masy mięśniowej @endif
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-bowl-food text-blue-600"
                                ></i>
                                Ilość treningów w tygodniu:</span
                            >
                            {{ $workout->meals_count }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            class="show-workout rounded-md bg-blue-600 text-white px-6 py-2 text-base w-28 duration-200 hover:bg-blue-700"
                        >
                            Zobacz
                        </button>
                        <button
                            class="ml-auto flex justify-center items-center"
                        >
                            <i
                                class="fa-solid fa-print text-xl text-blue-600"
                            ></i>
                        </button>
                        <form
                            class="flex justify-center items-center"
                            action="#"
                            method="post"
                        >
                            @csrf @METHOD('DELETE')
                            <button class="flex justify-center items-center">
                                <i
                                    class="fa-solid fa-trash text-xl text-red-600"
                                ></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty @endforelse @forelse($trashedWorkouts as $workout)
                <div
                    class="deleted-workout w-full border-[1px] border-slate-200 p-4 rounded-md flex flex-col gap-4 shadow-xs hidden"
                >
                    <h2 class="text-lg font-semibold text-blue-600">
                        {{ $workout->workout_name }}
                    </h2>
                    <div class="flex flex-col gap-2">
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-utensils text-blue-600"
                                ></i>
                                Cel:
                            </span>
                            @if($workout->workout_type === 'lose') Schudnięcie
                            @elseif($workout->workout_type === 'gain')
                            Zwiększenie masy mięśniowej @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <form
                            class="flex justify-center items-center"
                            action="{{ route('workout.restore', $workout) }}"
                            method="post"
                        >
                            @csrf @METHOD('POST')
                            <button
                                class="rounded-md bg-blue-600 text-white px-6 py-2 w-32 duration-200 hover:bg-blue-700 text-base"
                            >
                                Przywróć
                            </button>
                        </form>
                        <form
                            class="flex justify-center items-center"
                            action="{{
                                route('workout.forceDestroy', $workout)
                            }}"
                            method="post"
                        >
                            @csrf @METHOD('DELETE')
                            <button
                                class="rounded-md bg-red-600 text-white px-6 py-2 w-fit duration-200 hover:bg-red-700 text-base"
                            >
                                Usuń na zawsze
                            </button>
                        </form>
                    </div>
                </div>
                @empty @endforelse
            </div>
        </div>
        <div
            class="overlay w-full fixed h-screen top-0 left-0 bg-black opacity-70 hidden"
        ></div>
    </div>
</x-app-layout>

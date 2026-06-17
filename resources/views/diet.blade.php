<x-app-layout>
    <div class="flex flex-wrap gap-6">
        <div
            class="flex flex-col gap-6 w-full bg-white rounded-md border-[1px] border-slate-200 p-6 h-fit lg:w-[calc(50%-12px)]"
        >
            <div class="flex flex-col gap-2">
                <h2 class="text-lg font-medium text-black">
                    Dostosuj nową dietę
                </h2>
                <span class="text-xs text-gray-400 font-normal"
                    >Dostosuj parametry swojej diety. Wirtualny asystent bierze
                    pod uwagę również dane z twojego profilu pacjenta.</span
                >
            </div>

            <form
                action="{{ route('diet.store') }}"
                class="w-full flex flex-col gap-6"
                method="post"
            >
                @csrf @METHOD('POST')
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div class="flex flex-col gap-1 text-sm w-full">
                        <label for="diet_name" class="dark:text-white"
                            >Nazwa diety
                            <span class="text-red-500">*</span></label
                        >
                        <input
                            type="text"
                            name="diet_name"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                            placeholder="Moja nowa dieta"
                            value="{{ old('diet_name') }}"
                        />
                        @error('diet_name')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div
                        class="flex flex-col gap-1 text-sm w-full 2xl:w-[calc(33.333%-12px)]"
                    >
                        <label for="diet_type" class="dark:text-white"
                            >Typ diety
                            <span class="text-red-500">*</span></label
                        >
                        <select
                            type="text"
                            name="diet_type"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                        >
                            <option value="">Wybierz typ diety</option>
                            <option value="classic">Klasyczna</option>
                            <option value="wegan">Wegetariańska</option>
                            <option value="keto">Ketogeniczna</option>
                        </select>
                        @error('diet_type')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div
                        class="flex flex-col gap-1 text-sm w-full 2xl:w-[calc(33.333%-12px)]"
                    >
                        <label for="diet_kcal" class="dark:text-white"
                            >Ilość kcal
                            <span class="text-red-500">*</span></label
                        >
                        <select
                            type="number"
                            name="diet_kcal"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 text-sm"
                        >
                            <option value="">Wybierz ilość kalorii</option>
                            <option value="1500">1500</option>
                            <option value="2000">2000</option>
                            <option value="2500">2500</option>
                            <option value="3000">3000</option>
                        </select>
                        @error('diet_kcal')
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
                        <label for="diet_like" class="dark:text-white"
                            >Podaj składniki, które lubisz jeść</label
                        >
                        <textarea
                            type="number"
                            name="diet_like"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 resize-none h-24 text-sm"
                            placeholder="Np. jabłka, ogórki..."
                        ></textarea>
                    </div>
                    <div
                        class="flex flex-col gap-1 w-full text-sm lg:w-[calc(50%-8px)]"
                    >
                        <label for="diet_not_like" class="dark:text-white"
                            >Podaj składniki, których nie lubisz jeść</label
                        >
                        <textarea
                            type="number"
                            name="diet_not_like"
                            step="100"
                            class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-400 p-2 resize-none h-24 text-sm"
                            placeholder="Np. pomidory, cebula..."
                        ></textarea>
                    </div>
                </div>
                <div class="flex flex-wrap w-full gap-4 max-w-4xl">
                    <div class="flex flex-col gap-1 w-full text-sm">
                        <label for="diet_other_notes" class="dark:text-white"
                            >Dodatkowe uwagi</label
                        >
                        <textarea
                            type="text"
                            name="diet_other_notes"
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
                    <h2 class="text-lg font-medium text-black">Twoje diety</h2>
                    <span class="text-xs text-gray-400 font-normal"
                        >Lista twoich zapisanych diet.</span
                    >
                </div>
                <button
                    class="show-deleted-diets rounded-md bg-red-100 text-red-500 px-6 py-2 text-sm duration-200 hover:bg-red-200"
                >
                    Usunięte diety
                </button>
                <button
                    class="show-my-diets rounded-md bg-blue-100 text-blue-500 px-6 py-2 text-sm duration-200 hover:bg-blue-200 hidden"
                >
                    Moje diety
                </button>
            </div>

            <div class="flex flex-wrap gap-4">
                @forelse($diets as $diet)
                <div
                    class="diet w-full border-[1px] border-slate-200 p-4 rounded-md flex flex-col gap-4 shadow-xs"
                >
                    <h2 class="text-lg font-semibold text-blue-600">
                        {{ $diet->diet_name }}
                    </h2>
                    <div class="flex flex-col gap-2">
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-utensils text-blue-600"
                                ></i>
                                Typ diety:
                            </span>
                            @if($diet->diet_type === 'keto') Ketogeniczna
                            @elseif($diet->diet_type === 'wegan') Wegetariańska
                            @elseif($diet->diet_type === 'classic') Klasyczna
                            @endif
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i class="fa-solid fa-bolt text-blue-600"></i>
                                Ilość kalorii:
                            </span>
                            {{ $diet->diet_kcal }}
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-bowl-food text-blue-600"
                                ></i>
                                Ilość posiłków:</span
                            >
                            {{ $diet->meals_count }}
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i class="fa-solid fa-heart text-blue-600"></i>
                                Składniki, które lubię:</span
                            >
                            <span
                                class="lowercase"
                                >{{ $diet->diet_like }}</span
                            >
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-circle-xmark text-blue-600"
                                ></i>
                                Składniki, których nie lubię:</span
                            >
                            <span
                                class="lowercase"
                                >{{ $diet->diet_not_like }}</span
                            >
                        </p>
                        <!-- {!! $diet->content !!} -->
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            class="show-diet rounded-md bg-blue-600 text-white px-6 py-2 text-base w-28 duration-200 hover:bg-blue-700"
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
                            action="{{ route('diet.destroy', $diet) }}"
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
                @empty @endforelse @forelse($trashedDiets as $diet)
                <div
                    class="deleted-diet w-full border-[1px] border-slate-200 p-4 rounded-md flex flex-col gap-4 shadow-xs hidden"
                >
                    <h2 class="text-lg font-semibold text-blue-600">
                        {{ $diet->diet_name }}
                    </h2>
                    <div class="flex flex-col gap-2">
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-utensils text-blue-600"
                                ></i>
                                Typ diety:
                            </span>
                            @if($diet->diet_type === 'keto') Ketogeniczna
                            @elseif($diet->diet_type === 'wegan') Wegetariańska
                            @elseif($diet->diet_type === 'classic') Klasyczna
                            @endif
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i class="fa-solid fa-bolt text-blue-600"></i>
                                Ilość kalorii:
                            </span>
                            {{ $diet->diet_kcal }}
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-bowl-food text-blue-600"
                                ></i>
                                Ilość posiłków:</span
                            >
                            {{ $diet->meals_count }}
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i class="fa-solid fa-heart text-blue-600"></i>
                                Składniki, które lubię:</span
                            >
                            <span
                                class="lowercase"
                                >{{ $diet->diet_like }}</span
                            >
                        </p>
                        <p
                            class="text-sm flex items-center w-full justify-start gap-1"
                        >
                            <span class="font-medium"
                                ><i
                                    class="fa-solid fa-circle-xmark text-blue-600"
                                ></i>
                                Składniki, których nie lubię:</span
                            >
                            <span
                                class="lowercase"
                                >{{ $diet->diet_not_like }}</span
                            >
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <form
                            class="flex justify-center items-center"
                            action="{{ route('diet.restore', $diet) }}"
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
                            action="{{ route('diet.forceDestroy', $diet) }}"
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

    @forelse($diets as $diet)
    <div
        class="diet-popup fixed top-1/2 left-1/2 translate-x-[-50%] translate-y-[-50%] p-4 bg-white rounded-lg max-h-[90vh] hidden w-full max-w-[700px] overflow-y-scroll"
    >
        <h2
            class="text-xl font-semibold text-blue-600 text-center pb-4 border-b-[1px] border-gray-100"
        >
            {{ $diet->diet_name }}
        </h2>
        <div class="flex flex-wrap gap-2"></div>

        <div
            class="flex flex-col overflow-y-scroll max-h-[70vh] p-4 rounded-xl"
        >
            {!! $diet->content !!}
        </div>
    </div>
    @empty @endforelse
</x-app-layout>
@section('scripts')

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const showDietBtns = [...document.querySelectorAll(".show-diet")];
        const overlay = document.querySelector(".overlay");
        const dietPopups = [...document.querySelectorAll(".diet-popup")];

        showDietBtns.forEach((btn, i) => {
            btn.addEventListener("click", () => {
                overlay.classList.remove("hidden");
                dietPopups[i].classList.remove("hidden");
            });
        });

        overlay.addEventListener("click", () => {
            overlay.classList.add("hidden");
            dietPopups.forEach((popup) => {
                popup.classList.add("hidden");
            });
        });

        const diets = [...document.querySelectorAll(".diet")];
        const showDeletedBtn = document.querySelector(".show-deleted-diets");
        const showMyDietsBtn = document.querySelector(".show-my-diets");
        const deletedDiets = [...document.querySelectorAll(".deleted-diet")];

        showDeletedBtn.addEventListener("click", () => {
            diets.forEach((diet) => {
                diet.classList.add("hidden");
            });

            deletedDiets.forEach((diet) => {
                diet.classList.remove("hidden");
            });

            showDeletedBtn.classList.add("hidden");
            showMyDietsBtn.classList.remove("hidden");
        });

        showMyDietsBtn.addEventListener("click", () => {
            deletedDiets.forEach((diet) => {
                diet.classList.add("hidden");
            });

            diets.forEach((diet) => {
                diet.classList.remove("hidden");
            });

            showMyDietsBtn.classList.add("hidden");
            showDeletedBtn.classList.remove("hidden");
        });
    });
</script>
@endsection

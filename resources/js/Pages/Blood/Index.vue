<script setup>
import MainLayout from "@/Layouts/MainLayout.vue";
defineOptions({
    layout: MainLayout,
});

import { useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LabInput from "@/Components/LabInput.vue";
import InputLabel from "@/Components/InputLabel.vue";

const page = usePage();
const user = computed(() => page.props.user || {});

const form = useForm({
    wbc: user.value.wbc ?? null,
    rbc: user.value.rbc ?? null,
    hgb: user.value.hgb ?? null,
    hct: user.value.hct ?? null,
    mcv: user.value.mcv ?? null,
    mch: user.value.mch ?? null,
    mchc: user.value.mchc ?? null,
    plt: user.value.plt ?? null,
    rdw_sd: user.value.rdw_sd ?? null,
    rdw_cv: user.value.rdw_cv ?? null,
    pdw: user.value.pdw ?? null,
    mpv: user.value.mpv ?? null,
    p_lcr: user.value.p_lcr ?? null,
    pct: user.value.pct ?? null,
    neu: user.value.neu ?? null,
    lym: user.value.lym ?? null,
    mono: user.value.mono ?? null,
    eos: user.value.eos ?? null,
    baso: user.value.baso ?? null,
    tsh: user.value.tsh ?? null,
    ast: user.value.ast ?? null,
    alt: user.value.alt ?? null,
    bilirubin: user.value.bilirubin ?? null,
    alp: user.value.alp ?? null,
    ggtp: user.value.ggtp ?? null,
    total_cholesterol: user.value.total_cholesterol ?? null,
    hdl_cholesterol: user.value.hdl_cholesterol ?? null,
    non_hdl_cholesterol: user.value.non_hdl_cholesterol ?? null,
    ldl_cholesterol: user.value.ldl_cholesterol ?? null,
    triglycerides: user.value.triglycerides ?? null,
});

const submit = () =>
    form.patch(route("blood.update"), {
        onSuccess: () => {
            const toastMagic = new ToastMagic();
            toastMagic.success(
                "Gotowe!",
                "Wyniki badań krwi zostały zapisane i przeanalizowane."
            );
        },
    });

onMounted(() => {
    const dropdownBtns = [...document.querySelectorAll(".dropdown-btn")];
    const dropdownContents = [
        ...document.querySelectorAll(".dropdown-content"),
    ];
    const dropdownArrows = [...document.querySelectorAll(".dropdown-arrow")];

    dropdownBtns.forEach((btn, i) => {
        btn.addEventListener("click", () => {
            dropdownContents[i].classList.toggle("hidden");
            dropdownArrows[i].classList.toggle("rotate-180");
        });
    });
});
</script>

<template class="overflow-y-auto">
    <Head>
        <title>Wyniki badań</title>
    </Head>
    <div class="flex flex-wrap gap-6">
        <div
            class="flex flex-col gap-6 w-full lg:w-[calc(50%-12px)] bg-white rounded-2xl shadow-xs p-6 h-fit"
        >
            <form class="w-full flex flex-col gap-6" @submit.prevent="submit">
                <div class="flex flex-col w-full gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2 items-center">
                            <div
                                class="flex justify-center items-center bg-blue-200 size-12 rounded-2xl"
                            >
                                <i
                                    class="fa-solid fa-flask text-blue-600 text-xl"
                                ></i>
                            </div>
                            <h4 class="text-2xl font-normal">Wyniki badań</h4>
                        </div>
                        <span class="text-sm text-gray-600"
                            >W tym miejscu mozesz wprowadzic wyniki swoich
                            badań.</span
                        >
                    </div>

                    <div class="w-full flex flex-wrap gap-4">
                        <!-- Morfologia -->
                        <div class="w-full flex justify-between items-center">
                            <h4 class="text-xl font-normal w-full">
                                Morfologia krwi
                            </h4>
                            <button
                                type="button"
                                class="dropdown-btn flex justify-center items-center"
                            >
                                <i
                                    class="dropdown-arrow fa-solid fa-angle-down text-xl"
                                ></i>
                            </button>
                        </div>

                        <div
                            class="dropdown-content w-full grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                        >
                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Leukocyty (WBC)" />
                                <LabInput
                                    v-model="form.wbc"
                                    unit="tys/μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.wbc"
                                    >{{ form.errors.wbc }}</span
                                >
                            </div>
                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Erytrocyty (RBC)" />
                                <LabInput
                                    v-model="form.rbc"
                                    unit="mln/μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.rbc"
                                    >{{ form.errors.rbc }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Hemoglobina (HGB)" />
                                <LabInput
                                    v-model="form.hgb"
                                    unit="g/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.hgb"
                                    >{{ form.errors.hgb }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Hematokryt (HCT)" />
                                <LabInput
                                    v-model="form.hct"
                                    unit="%"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.hct"
                                    >{{ form.errors.hct }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="MCV" />
                                <LabInput
                                    v-model="form.mcv"
                                    unit="fl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.mcv"
                                    >{{ form.errors.mcv }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="MCH" />
                                <LabInput
                                    v-model="form.mch"
                                    unit="pg"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.mch"
                                    >{{ form.errors.mch }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="MCHC" />
                                <LabInput
                                    v-model="form.mchc"
                                    unit="g/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.mchc"
                                    >{{ form.errors.mchc }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Płytki krwi (PLT)" />
                                <LabInput
                                    v-model="form.plt"
                                    unit="tys./μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.plt"
                                    >{{ form.errors.plt }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="RDW-SD" />
                                <LabInput
                                    v-model="form.rdw_sd"
                                    unit="fl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.rdw_sd"
                                    >{{ form.errors.rdw_sd }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="RDW-CV" />
                                <LabInput
                                    v-model="form.rdw_cv"
                                    unit="%"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.rdw_cv"
                                    >{{ form.errors.rdw_cv }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="PDW" />
                                <LabInput
                                    v-model="form.pdw"
                                    unit="fl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.pdw"
                                    >{{ form.errors.pdw }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="MPV" />
                                <LabInput
                                    v-model="form.mpv"
                                    unit="fl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.mpv"
                                    >{{ form.errors.mpv }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="P-LCR" />
                                <LabInput
                                    v-model="form.p_lcr"
                                    unit="%"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.p_lcr"
                                    >{{ form.errors.p_lcr }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="PCT" />
                                <LabInput
                                    v-model="form.pct"
                                    unit="%"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.pct"
                                    >{{ form.errors.pct }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Neutrofile (NEU)" />
                                <LabInput
                                    v-model="form.neu"
                                    unit="tys./μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.neu"
                                    >{{ form.errors.neu }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Limfocyty (LYM)" />
                                <LabInput
                                    v-model="form.lym"
                                    unit="tys./μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.lym"
                                    >{{ form.errors.lym }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Monocyty (MON)" />
                                <LabInput
                                    v-model="form.mono"
                                    unit="tys./μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.mono"
                                    >{{ form.errors.mono }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Eozynofile (EOS)" />
                                <LabInput
                                    v-model="form.eos"
                                    unit="tys./μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.eos"
                                    >{{ form.errors.eos }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Bazofile (BAS)" />
                                <LabInput
                                    v-model="form.baso"
                                    unit="tys./μl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.baso"
                                    >{{ form.errors.baso }}</span
                                >
                            </div>
                        </div>
                        <!-- Morfologia -->

                        <!-- Próby watrobowe -->
                        <div
                            class="mt-6 w-full flex justify-between items-center"
                        >
                            <h4 class="text-xl font-normal w-full">
                                Próby wątrobowe
                            </h4>
                            <button
                                type="button"
                                class="dropdown-btn flex justify-center items-center"
                            >
                                <i
                                    class="dropdown-arrow fa-solid fa-angle-down text-xl"
                                ></i>
                            </button>
                        </div>

                        <div
                            class="dropdown-content w-full grid gap-4 grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="AST" />
                                <LabInput
                                    v-model="form.ast"
                                    unit="U/l"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.ast"
                                    >{{ form.errors.ast }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="ALT" />
                                <LabInput
                                    v-model="form.alt"
                                    unit="U/l"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.alt"
                                    >{{ form.errors.alt }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Bilirubina całkowita" />
                                <LabInput
                                    v-model="form.bilirubin"
                                    unit="mg/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.bilirubin"
                                    >{{ form.errors.bilirubin }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Fosfataza zasadowa (ALP)" />
                                <LabInput
                                    v-model="form.alp"
                                    unit="U/l"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.alp"
                                    >{{ form.errors.alp }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="GGTP" />
                                <LabInput
                                    v-model="form.ggtp"
                                    unit="U/l"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.ggtp"
                                    >{{ form.errors.ggtp }}</span
                                >
                            </div>
                        </div>
                        <!-- Próby watrobowe -->

                        <!-- Cholesterol -->
                        <div
                            class="mt-6 w-full flex justify-between items-center"
                        >
                            <h4 class="text-xl font-normal w-full">
                                Cholesterol
                            </h4>
                            <button
                                type="button"
                                class="dropdown-btn flex justify-center items-center"
                            >
                                <i
                                    class="dropdown-arrow fa-solid fa-angle-down text-xl"
                                ></i>
                            </button>
                        </div>

                        <div
                            class="dropdown-content w-full grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                        >
                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Cholesterol całk. (CHOL)" />
                                <LabInput
                                    v-model="form.total_cholesterol"
                                    unit="mg/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.total_cholesterol"
                                    >{{ form.errors.total_cholesterol }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Cholesterol HDL" />
                                <LabInput
                                    v-model="form.hdl_cholesterol"
                                    unit="mg/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.hdl_cholesterol"
                                    >{{ form.errors.hdl_cholesterol }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Cholesterol nie-HDL" />
                                <LabInput
                                    v-model="form.non_hdl_cholesterol"
                                    unit="mg/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.non_hdl_cholesterol"
                                    >{{ form.errors.non_hdl_cholesterol }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Cholesterol LDL" />
                                <LabInput
                                    v-model="form.ldl_cholesterol"
                                    unit="mg/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.ldl_cholesterol"
                                    >{{ form.errors.ldl_cholesterol }}</span
                                >
                            </div>

                            <div class="flex flex-col gap-1 text-sm">
                                <InputLabel value="Triglicerydy (TG)" />
                                <LabInput
                                    v-model="form.triglycerides"
                                    unit="mg/dl"
                                ></LabInput>
                                <span
                                    class="text-red-500 text-xs"
                                    v-if="form.errors.triglycerides"
                                    >{{ form.errors.triglycerides }}</span
                                >
                            </div>
                        </div>
                        <!-- Cholesterol -->
                    </div>
                </div>

                <div class="w-full">
                    <PrimaryButton
                        :type="'submit'"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        >Zapisz</PrimaryButton
                    >
                </div>
            </form>
        </div>

        <!-- Zalecenia specjalisty -->
        <div
            class="flex flex-col gap-4 w-full lg:w-[calc(50%-12px)] bg-white rounded-2xl shadow-xs p-6 h-fit sticky lg:top-[87px] border border-slate-100"
        >
            <div class="flex gap-3 items-center">
                <div
                    class="flex justify-center items-center bg-emerald-100 size-11 rounded-xl"
                >
                    <i
                        class="fa-solid fa-user-doctor text-emerald-600 text-lg"
                    ></i>
                </div>
                <div class="flex flex-col">
                    <h4 class="text-[19px] font-semibold text-slate-900 leading-6">
                        Zalecenia wirtualnego specjalisty
                    </h4>
                    <span class="text-xs text-slate-500">Personalizowana analiza AI</span>
                </div>
            </div>

            <div v-if="user.blood_recommendations" class="space-y-3">
                <p class="text-sm text-slate-600">Ocena na podstawie twoich wyników badań — wygenerowana przez AI.</p>
                <div
                    v-html="user.blood_recommendations"
                    class="prose prose-sm max-w-none prose-p:my-2.5 prose-p:leading-7 prose-p:text-slate-700 prose-strong:font-semibold prose-strong:text-slate-900 prose-b:font-semibold prose-b:text-slate-900 prose-ul:my-3 prose-ul:list-disc prose-ul:pl-5 prose-ol:my-3 prose-ol:list-decimal prose-ol:pl-5 prose-li:my-1.5 prose-li:text-slate-700 marker:text-emerald-500 prose-headings:text-slate-900 prose-headings:font-semibold"
                ></div>
                <p class="flex gap-2 text-[11px] leading-4 text-slate-500">
                    <i class="fa-solid fa-circle-info text-slate-400 mt-0.5 shrink-0"></i>
                    <span>To wskazówki AI — mogą być niepełne, zawsze skonsultuj decyzje zdrowotne z lekarzem.</span>
                </p>
            </div>
            <div v-else class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-5 py-8 text-center">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-white border border-slate-100 shadow-sm">
                    <i class="fa-solid fa-wand-magic-sparkles text-emerald-500"></i>
                </div>
                <p class="mt-4 text-sm font-medium text-slate-700">Brak rekomendacji</p>
                <p class="mt-1 text-sm text-slate-500">Wypełnij co najmniej jeden wynik po lewej, aby wygenerować spersonalizowane zalecenia.</p>
                <p class="mt-2 text-xs text-slate-400">Analiza zajmuje kilka sekund.</p>
            </div>
        </div>
    </div>
</template>

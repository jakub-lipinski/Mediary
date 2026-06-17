<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

import { onMounted } from "vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

onMounted(() => {
    const passwordInputs = [...document.querySelectorAll(".password-input")];
    const togglePasswordBtns = [
        ...document.querySelectorAll(".toggle-password"),
    ];
    const toggleIcons = [...document.querySelectorAll(".toggle-icon")];

    togglePasswordBtns.forEach((btn, i) => {
        btn.addEventListener("click", () => {
            if (passwordInputs[i].type === "password") {
                passwordInputs[i].type = "text";
                toggleIcons[i].classList.remove("fa-eye");
                toggleIcons[i].classList.add("fa-eye-slash");
            } else {
                passwordInputs[i].type = "password";
                toggleIcons[i].classList.remove("fa-eye-slash");
                toggleIcons[i].classList.add("fa-eye");
            }
        });
    });
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Zarejestruj się" />

    <div class="flex flex-row min-h-screen">
        <div class="w-full md:w-1/2 flex flex-col justify-center items-center">
            <div
                class="flex w-[90%] h-full justify-center items-center max-w-[500px] flex-col gap-6"
            >
                <form class="w-full" @submit.prevent="submit">
                    <h2
                        class="text-center text-2xl font-semibold mb-4 md:text-3xl"
                    >
                        Załóż konto
                    </h2>
                    <p class="text-center text-sm text-gray-600">
                        Wypełnij dane, aby utworzyć konto pacjenta.
                    </p>
                    <div class="flex flex-col gap-6 mt-6">
                        <div>
                            <label class="text-gray-600 text-xs" for="email"
                                >Imię</label
                            >
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <div>
                            <label class="text-gray-600 text-xs" for="password"
                                >Adres email</label
                            >
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                                required
                                autocomplete="username"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <div class="relative">
                            <label class="text-gray-600 text-xs" for="password"
                                >Hasło</label
                            >
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="password-input w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 pr-10 text-sm"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="toggle-password absolute top-1/2 right-2"
                            >
                                <i
                                    class="toggle-icon fa-solid fa-eye text-gray-600"
                                ></i>
                            </button>
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <div class="relative">
                            <label class="text-gray-600 text-xs" for="password"
                                >Potwierdź hasło</label
                            >
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="password-input w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 pr-10 text-sm"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="toggle-password absolute top-1/2 right-2"
                            >
                                <i
                                    class="toggle-icon fa-solid fa-eye text-gray-600"
                                ></i>
                            </button>
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            :type="'submit'"
                            class="w-full"
                            >Zarejestruj się</PrimaryButton
                        >
                    </div>
                </form>
                <div class="relative w-full h-[1px] bg-slate-300">
                    <span
                        class="absolute top-1/2 left-1/2 bg-white text-gray-600 text-sm px-2 translate-x-[-50%] translate-y-[-50%]"
                        >lub</span
                    >
                </div>
                <a
                    :href="route('google.redirect')"
                    class="w-full flex gap-2 text-sm border rounded-full px-4 py-2 bg-gray-50 border-slate-300 justify-center items-center duration-200 hover:bg-gray-100"
                >
                    Zarejestruj się z
                    <img class="size-4" src="/img/logo_google.png" alt="" />
                </a>
                <p class="text-sm text-gray-600">
                    Masz już konto?
                    <Link
                        :href="route('login')"
                        class="text-blue-600 duration-200 hover:text-blue-700"
                        >Zaloguj się tutaj.</Link
                    >
                </p>
            </div>
        </div>
        <div class="hidden md:flex md:w-1/2 md:h-screen md:p-4">
            <div class="w-full rounded-2xl h-full overflow-hidden">
                <img
                    src="/img/login_screen.jpeg"
                    class="w-full h-full object-cover"
                    alt=""
                />
            </div>
        </div>
    </div>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
    </div>

    <!-- <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div
                v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                class="mt-4"
            >
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            name="terms"
                            required
                        />

                        <div class="ms-2">
                            I agree to the
                            <a
                                target="_blank"
                                :href="route('terms.show')"
                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >Terms of Service</a
                            >
                            and
                            <a
                                target="_blank"
                                :href="route('policy.show')"
                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >Privacy Policy</a
                            >
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard> -->
</template>

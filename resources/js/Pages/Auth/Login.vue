<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { onMounted } from "vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

onMounted(() => {
    const passwordInput = document.getElementById("password");
    const togglePasswordBtn = document.getElementById("toggle-password");
    const toggleIcon = document.getElementById("toggle-icon");

    togglePasswordBtn.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    });
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Zaloguj się" />
    <div class="flex flex-row min-h-screen">
        <div class="w-full md:w-1/2 flex flex-col justify-center items-center">
            <div
                class="flex w-[90%] h-full justify-center items-center max-w-[500px] flex-col gap-6"
            >
                <form class="w-full" @submit.prevent="submit">
                    <h2
                        class="text-center text-2xl font-semibold mb-4 md:text-3xl"
                    >
                        Witaj ponownie!
                    </h2>
                    <p class="text-center text-sm text-gray-600">
                        Wprowadź dane i zaloguj się do swojego konta pacjenta.
                    </p>
                    <div class="flex flex-col gap-6 mt-6">
                        <div>
                            <label class="text-gray-600 text-xs" for="email"
                                >Adres email</label
                            >
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 text-sm"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
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
                                class="w-full rounded-md bg-[#FFF] border-[1px] border-slate-300 p-2 pr-10 text-sm"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-2"
                                id="toggle-password"
                            >
                                <i
                                    id="toggle-icon"
                                    class="fa-solid fa-eye text-gray-600"
                                ></i>
                            </button>
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <div class="flex items-center w-full justify-between">
                            <label class="flex items-center">
                                <Checkbox
                                    v-model:checked="form.remember"
                                    name="remember"
                                />
                                <span class="ms-2 text-sm text-gray-600"
                                    >Nie wylogowuj mnie</span
                                >
                            </label>
                            <Link
                                class="text-sm text-blue-600"
                                :href="route('password.request')"
                                >Zapomniałem/am hasła</Link
                            >
                        </div>

                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            :type="'submit'"
                            class="w-full"
                            >Zaloguj się</PrimaryButton
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
                    Zaloguj się z
                    <img class="size-4" src="/img/logo_google.png" alt="" />
                </a>
                <p class="text-sm text-gray-600">
                    Nie masz jeszcze konta?
                    <Link
                        :href="route('register')"
                        class="text-blue-600 duration-200 hover:text-blue-700"
                        >Zarejestruj się tutaj.</Link
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

    <!-- <form @submit.prevent="submit">
       

       

        <div class="flex items-center justify-end mt-4">
            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Forgot your password?
            </Link>

            <PrimaryButton
                class="ms-4"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Log in
            </PrimaryButton>
        </div>
    </form> -->
</template>

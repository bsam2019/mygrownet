<script setup lang="ts">
import { ref, computed } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';

interface Country {
    code: string;
    name: string;
    flag: string;
}

interface Props {
    modelValue: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const showDropdown = ref(false);
const searchQuery = ref('');

// African countries
const countries: Country[] = [
    { code: 'ZM', name: 'Zambia', flag: 'zm' },
    { code: 'ZA', name: 'South Africa', flag: 'za' },
    { code: 'DZ', name: 'Algeria', flag: 'dz' },
    { code: 'AO', name: 'Angola', flag: 'ao' },
    { code: 'BJ', name: 'Benin', flag: 'bj' },
    { code: 'BW', name: 'Botswana', flag: 'bw' },
    { code: 'BF', name: 'Burkina Faso', flag: 'bf' },
    { code: 'BI', name: 'Burundi', flag: 'bi' },
    { code: 'CM', name: 'Cameroon', flag: 'cm' },
    { code: 'CV', name: 'Cape Verde', flag: 'cv' },
    { code: 'CF', name: 'Central African Republic', flag: 'cf' },
    { code: 'TD', name: 'Chad', flag: 'td' },
    { code: 'KM', name: 'Comoros', flag: 'km' },
    { code: 'CG', name: 'Congo', flag: 'cg' },
    { code: 'CD', name: 'DR Congo', flag: 'cd' },
    { code: 'CI', name: "Côte d'Ivoire", flag: 'ci' },
    { code: 'DJ', name: 'Djibouti', flag: 'dj' },
    { code: 'EG', name: 'Egypt', flag: 'eg' },
    { code: 'GQ', name: 'Equatorial Guinea', flag: 'gq' },
    { code: 'ER', name: 'Eritrea', flag: 'er' },
    { code: 'SZ', name: 'Eswatini', flag: 'sz' },
    { code: 'ET', name: 'Ethiopia', flag: 'et' },
    { code: 'GA', name: 'Gabon', flag: 'ga' },
    { code: 'GM', name: 'Gambia', flag: 'gm' },
    { code: 'GH', name: 'Ghana', flag: 'gh' },
    { code: 'GN', name: 'Guinea', flag: 'gn' },
    { code: 'GW', name: 'Guinea-Bissau', flag: 'gw' },
    { code: 'KE', name: 'Kenya', flag: 'ke' },
    { code: 'LS', name: 'Lesotho', flag: 'ls' },
    { code: 'LR', name: 'Liberia', flag: 'lr' },
    { code: 'LY', name: 'Libya', flag: 'ly' },
    { code: 'MG', name: 'Madagascar', flag: 'mg' },
    { code: 'MW', name: 'Malawi', flag: 'mw' },
    { code: 'ML', name: 'Mali', flag: 'ml' },
    { code: 'MR', name: 'Mauritania', flag: 'mr' },
    { code: 'MU', name: 'Mauritius', flag: 'mu' },
    { code: 'MA', name: 'Morocco', flag: 'ma' },
    { code: 'MZ', name: 'Mozambique', flag: 'mz' },
    { code: 'NA', name: 'Namibia', flag: 'na' },
    { code: 'NE', name: 'Niger', flag: 'ne' },
    { code: 'NG', name: 'Nigeria', flag: 'ng' },
    { code: 'RW', name: 'Rwanda', flag: 'rw' },
    { code: 'ST', name: 'São Tomé and Príncipe', flag: 'st' },
    { code: 'SN', name: 'Senegal', flag: 'sn' },
    { code: 'SC', name: 'Seychelles', flag: 'sc' },
    { code: 'SL', name: 'Sierra Leone', flag: 'sl' },
    { code: 'SO', name: 'Somalia', flag: 'so' },
    { code: 'SS', name: 'South Sudan', flag: 'ss' },
    { code: 'SD', name: 'Sudan', flag: 'sd' },
    { code: 'TZ', name: 'Tanzania', flag: 'tz' },
    { code: 'TG', name: 'Togo', flag: 'tg' },
    { code: 'TN', name: 'Tunisia', flag: 'tn' },
    { code: 'UG', name: 'Uganda', flag: 'ug' },
    { code: 'ZW', name: 'Zimbabwe', flag: 'zw' },
];

const selectedCountry = computed(() => {
    return countries.find(c => c.name === props.modelValue) || countries[0];
});

const filteredCountries = computed(() => {
    if (!searchQuery.value) return countries;
    const query = searchQuery.value.toLowerCase();
    return countries.filter(c => 
        c.name.toLowerCase().includes(query)
    );
});

const selectCountry = (country: Country) => {
    emit('update:modelValue', country.name);
    showDropdown.value = false;
    searchQuery.value = '';
};
</script>

<template>
    <div class="relative">
        <!-- Selected Country Display -->
        <button
            type="button"
            @click="showDropdown = !showDropdown"
            class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-left"
        >
            <img 
                :src="`https://flagcdn.com/w40/${selectedCountry.flag}.png`"
                :alt="selectedCountry.name"
                class="w-6 h-4 object-cover rounded flex-shrink-0"
            />
            <span class="flex-1 text-sm text-gray-900">{{ selectedCountry.name }}</span>
            <ChevronDownIcon class="w-4 h-4 text-gray-500 flex-shrink-0" aria-hidden="true" />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute z-50 mt-2 w-full bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 overflow-hidden"
            >
                <!-- Search -->
                <div class="p-3 border-b border-gray-200">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search country..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @click.stop
                    />
                </div>

                <!-- Countries List -->
                <div class="overflow-y-auto max-h-80">
                    <button
                        v-for="country in filteredCountries"
                        :key="country.code"
                        type="button"
                        @click="selectCountry(country)"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 transition text-left"
                        :class="{ 'bg-blue-50': country.name === selectedCountry.name }"
                    >
                        <img 
                            :src="`https://flagcdn.com/w40/${country.flag}.png`"
                            :alt="country.name"
                            class="w-6 h-4 object-cover rounded flex-shrink-0"
                        />
                        <span class="flex-1 text-sm text-gray-900">{{ country.name }}</span>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Click outside to close -->
        <div
            v-if="showDropdown"
            @click="showDropdown = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

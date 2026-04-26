<script setup lang="ts">
import { ref, computed } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/solid';

interface Country {
    code: string;
    name: string;
    dialCode: string;
    flag: string;
}

interface Props {
    modelValue: string;
    countryCode: string;
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: '97X XXX XXX'
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    'update:countryCode': [value: string];
}>();

const showDropdown = ref(false);
const searchQuery = ref('');

// African countries with their dial codes
const countries: Country[] = [
    { code: 'ZM', name: 'Zambia', dialCode: '+260', flag: 'zm' },
    { code: 'ZA', name: 'South Africa', dialCode: '+27', flag: 'za' },
    { code: 'DZ', name: 'Algeria', dialCode: '+213', flag: 'dz' },
    { code: 'AO', name: 'Angola', dialCode: '+244', flag: 'ao' },
    { code: 'BJ', name: 'Benin', dialCode: '+229', flag: 'bj' },
    { code: 'BW', name: 'Botswana', dialCode: '+267', flag: 'bw' },
    { code: 'BF', name: 'Burkina Faso', dialCode: '+226', flag: 'bf' },
    { code: 'BI', name: 'Burundi', dialCode: '+257', flag: 'bi' },
    { code: 'CM', name: 'Cameroon', dialCode: '+237', flag: 'cm' },
    { code: 'CV', name: 'Cape Verde', dialCode: '+238', flag: 'cv' },
    { code: 'CF', name: 'Central African Republic', dialCode: '+236', flag: 'cf' },
    { code: 'TD', name: 'Chad', dialCode: '+235', flag: 'td' },
    { code: 'KM', name: 'Comoros', dialCode: '+269', flag: 'km' },
    { code: 'CG', name: 'Congo', dialCode: '+242', flag: 'cg' },
    { code: 'CD', name: 'DR Congo', dialCode: '+243', flag: 'cd' },
    { code: 'CI', name: "Côte d'Ivoire", dialCode: '+225', flag: 'ci' },
    { code: 'DJ', name: 'Djibouti', dialCode: '+253', flag: 'dj' },
    { code: 'EG', name: 'Egypt', dialCode: '+20', flag: 'eg' },
    { code: 'GQ', name: 'Equatorial Guinea', dialCode: '+240', flag: 'gq' },
    { code: 'ER', name: 'Eritrea', dialCode: '+291', flag: 'er' },
    { code: 'SZ', name: 'Eswatini', dialCode: '+268', flag: 'sz' },
    { code: 'ET', name: 'Ethiopia', dialCode: '+251', flag: 'et' },
    { code: 'GA', name: 'Gabon', dialCode: '+241', flag: 'ga' },
    { code: 'GM', name: 'Gambia', dialCode: '+220', flag: 'gm' },
    { code: 'GH', name: 'Ghana', dialCode: '+233', flag: 'gh' },
    { code: 'GN', name: 'Guinea', dialCode: '+224', flag: 'gn' },
    { code: 'GW', name: 'Guinea-Bissau', dialCode: '+245', flag: 'gw' },
    { code: 'KE', name: 'Kenya', dialCode: '+254', flag: 'ke' },
    { code: 'LS', name: 'Lesotho', dialCode: '+266', flag: 'ls' },
    { code: 'LR', name: 'Liberia', dialCode: '+231', flag: 'lr' },
    { code: 'LY', name: 'Libya', dialCode: '+218', flag: 'ly' },
    { code: 'MG', name: 'Madagascar', dialCode: '+261', flag: 'mg' },
    { code: 'MW', name: 'Malawi', dialCode: '+265', flag: 'mw' },
    { code: 'ML', name: 'Mali', dialCode: '+223', flag: 'ml' },
    { code: 'MR', name: 'Mauritania', dialCode: '+222', flag: 'mr' },
    { code: 'MU', name: 'Mauritius', dialCode: '+230', flag: 'mu' },
    { code: 'MA', name: 'Morocco', dialCode: '+212', flag: 'ma' },
    { code: 'MZ', name: 'Mozambique', dialCode: '+258', flag: 'mz' },
    { code: 'NA', name: 'Namibia', dialCode: '+264', flag: 'na' },
    { code: 'NE', name: 'Niger', dialCode: '+227', flag: 'ne' },
    { code: 'NG', name: 'Nigeria', dialCode: '+234', flag: 'ng' },
    { code: 'RW', name: 'Rwanda', dialCode: '+250', flag: 'rw' },
    { code: 'ST', name: 'São Tomé and Príncipe', dialCode: '+239', flag: 'st' },
    { code: 'SN', name: 'Senegal', dialCode: '+221', flag: 'sn' },
    { code: 'SC', name: 'Seychelles', dialCode: '+248', flag: 'sc' },
    { code: 'SL', name: 'Sierra Leone', dialCode: '+232', flag: 'sl' },
    { code: 'SO', name: 'Somalia', dialCode: '+252', flag: 'so' },
    { code: 'SS', name: 'South Sudan', dialCode: '+211', flag: 'ss' },
    { code: 'SD', name: 'Sudan', dialCode: '+249', flag: 'sd' },
    { code: 'TZ', name: 'Tanzania', dialCode: '+255', flag: 'tz' },
    { code: 'TG', name: 'Togo', dialCode: '+228', flag: 'tg' },
    { code: 'TN', name: 'Tunisia', dialCode: '+216', flag: 'tn' },
    { code: 'UG', name: 'Uganda', dialCode: '+256', flag: 'ug' },
    { code: 'ZW', name: 'Zimbabwe', dialCode: '+263', flag: 'zw' },
];

const selectedCountry = computed(() => {
    return countries.find(c => c.dialCode === props.countryCode) || countries[0];
});

const filteredCountries = computed(() => {
    if (!searchQuery.value) return countries;
    const query = searchQuery.value.toLowerCase();
    return countries.filter(c => 
        c.name.toLowerCase().includes(query) || 
        c.dialCode.includes(query)
    );
});

const selectCountry = (country: Country) => {
    emit('update:countryCode', country.dialCode);
    showDropdown.value = false;
    searchQuery.value = '';
};

const updatePhone = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div class="relative">
        <div class="flex gap-2">
            <!-- Country Selector -->
            <div class="relative">
                <button
                    type="button"
                    @click="showDropdown = !showDropdown"
                    class="flex items-center gap-2 px-3 py-3 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                >
                    <img 
                        :src="`https://flagcdn.com/w40/${selectedCountry.flag}.png`"
                        :alt="selectedCountry.name"
                        class="w-6 h-4 object-cover rounded"
                    />
                    <span class="text-sm font-medium text-gray-700">{{ selectedCountry.dialCode }}</span>
                    <ChevronDownIcon class="w-4 h-4 text-gray-500" aria-hidden="true" />
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
                        class="absolute z-50 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 overflow-hidden"
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
                                :class="{ 'bg-blue-50': country.dialCode === selectedCountry.dialCode }"
                            >
                                <img 
                                    :src="`https://flagcdn.com/w40/${country.flag}.png`"
                                    :alt="country.name"
                                    class="w-6 h-4 object-cover rounded flex-shrink-0"
                                />
                                <span class="flex-1 text-sm text-gray-900">{{ country.name }}</span>
                                <span class="text-sm text-gray-500 font-medium">{{ country.dialCode }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Phone Input -->
            <input
                :value="modelValue"
                @input="updatePhone"
                type="tel"
                :placeholder="placeholder"
                class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            />
        </div>

        <!-- Click outside to close -->
        <div
            v-if="showDropdown"
            @click="showDropdown = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

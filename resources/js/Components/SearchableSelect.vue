<template>
  <div class="relative w-full" ref="container">
    <div
      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus-within:ring-2 focus-within:ring-blue-500 flex justify-between items-center bg-white cursor-text"
      @click="openDropdown"
    >
      <input
        ref="searchInput"
        v-model="searchQuery"
        type="text"
        class="w-full bg-transparent outline-none p-0 border-none focus:ring-0 text-gray-700"
        :class="{ 'opacity-60 cursor-not-allowed': disabled }"
        :placeholder="isOpen ? 'Cari...' : selectedLabel || placeholder"
        :disabled="disabled"
        @focus="openDropdown"
        @input="isOpen = true"
        @keydown.esc="closeDropdown"
      />
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-4 text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
      >
        <ul class="py-1 text-sm text-gray-700">
          <li
            v-if="filteredOptions.length === 0"
            class="px-3 py-2 text-gray-500 italic"
          >
            Tidak ditemukan.
          </li>
          <!-- Opsi kosong/reset (jika diperlukan) -->
          <li
             @click.stop="selectOption({ [valueKey]: '', [labelKey]: '-- Kosongkan --' })"
             class="px-3 py-2 cursor-pointer hover:bg-red-50 hover:text-red-700 text-gray-500 italic"
             v-if="!searchQuery"
          >
            -- Kosongkan --
          </li>
          <li
            v-for="option in filteredOptions"
            :key="option[valueKey]"
            @click.stop="selectOption(option)"
            class="px-3 py-2 cursor-pointer hover:bg-blue-50 hover:text-blue-700"
            :class="{ 'bg-blue-100 text-blue-900 font-medium': modelValue === option[valueKey] }"
          >
            {{ option[labelKey] }}
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
  options: {
    type: Array as () => any[],
    default: () => [],
  },
  valueKey: {
    type: String,
    default: 'id',
  },
  labelKey: {
    type: String,
    default: 'nama',
  },
  placeholder: {
    type: String,
    default: '-- Pilih --',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const container = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

const selectedLabel = computed(() => {
  const selected = props.options.find(opt => opt[props.valueKey] === props.modelValue);
  return selected ? selected[props.labelKey] : '';
});

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter(opt =>
    String(opt[props.labelKey]).toLowerCase().includes(q)
  );
});

const openDropdown = () => {
  if (props.disabled) return;
  if (!isOpen.value) {
    isOpen.value = true;
    searchQuery.value = ''; // Reset search on open to show all
  }
  nextTick(() => {
    searchInput.value?.focus();
  });
};

const closeDropdown = () => {
  isOpen.value = false;
  searchQuery.value = ''; // Clear search so input shows placeholder/label
};

const selectOption = (option: any) => {
  emit('update:modelValue', option[props.valueKey]);
  closeDropdown();
};

const handleClickOutside = (event: MouseEvent) => {
  if (container.value && !container.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>

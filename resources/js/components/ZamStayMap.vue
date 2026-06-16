<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
  latitude?: number;
  longitude?: number;
  markers?: { lat: number; lng: number; title?: string; price?: string; id?: number }[];
  title?: string;
  height?: string;
  zoom?: number;
}>();

const mapEl = ref<HTMLDivElement>();
const mapReady = ref(false);

const loadLeaflet = (): Promise<any> => {
  return new Promise((resolve) => {
    if ((window as any).L) return resolve((window as any).L);
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => resolve((window as any).L);
    document.head.appendChild(script);
  });
};

const initMap = async () => {
  const L = await loadLeaflet();
  if (!mapEl.value) return;

  let center: [number, number];
  let zoom = props.zoom || 13;

  if (props.markers && props.markers.length > 1) {
    const lats = props.markers.map(m => m.lat);
    const lngs = props.markers.map(m => m.lng);
    center = [
      (Math.min(...lats) + Math.max(...lats)) / 2,
      (Math.min(...lngs) + Math.max(...lngs)) / 2,
    ];
    zoom = props.zoom || 7;
  } else if (props.latitude && props.longitude) {
    center = [props.latitude, props.longitude];
  } else {
    center = [-14.5, 28.0];
    zoom = 6;
  }

  const map = L.map(mapEl.value).setView(center, zoom);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  const markers = props.markers && props.markers.length > 0
    ? props.markers
    : (props.latitude && props.longitude
        ? [{ lat: props.latitude, lng: props.longitude, title: props.title }]
        : []);

  markers.forEach(m => {
    const marker = L.marker([m.lat, m.lng]).addTo(map);
    if (m.title) {
      const html = m.price
        ? `<b>${m.title}</b><br/>ZMW ${m.price}/night`
        : m.title;
      marker.bindPopup(html);
    }
  });

  if (markers.length > 1) {
    const group = L.featureGroup(markers.map((m: any) =>
      L.marker([m.lat, m.lng])
    ));
    map.fitBounds(group.getBounds().pad(0.1));
  }

  mapReady.value = true;
};

onMounted(initMap);

watch(() => [props.latitude, props.longitude, props.markers], () => {
  mapReady.value = false;
  if (mapEl.value) {
    mapEl.value.innerHTML = '';
    initMap();
  }
});
</script>

<template>
  <div ref="mapEl" class="rounded-2xl overflow-hidden border border-gray-200" :style="{ height: height || '320px' }"></div>
</template>

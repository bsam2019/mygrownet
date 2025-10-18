<template>
  <div class="flow-root">
    <ul role="list" class="-mb-8">
      <li v-for="(event, eventIdx) in events" :key="eventIdx">
        <div class="relative pb-8">
          <span v-if="eventIdx !== events.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
          <div class="relative flex space-x-3">
            <div>
              <span :class="[
                event.iconBackground,
                'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white'
              ]">
                <component :is="event.icon" class="h-5 w-5 text-white" aria-hidden="true" />
              </span>
            </div>
            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
              <div>
                <p class="text-sm text-gray-500">{{ event.title }}</p>
                <p class="mt-0.5 text-sm text-gray-900">{{ event.description }}</p>
              </div>
              <div class="text-right text-sm whitespace-nowrap text-gray-500">
                <time :datetime="event.date">{{ formatDate(event.date) }}</time>
              </div>
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import { computed } from 'vue';
import { 
  CurrencyDollarIcon, 
  ChartBarIcon, 
  ClockIcon, 
  ArrowPathIcon 
} from '@heroicons/vue/24/solid';

export default {
  name: 'InvestmentTimeline',
  components: {
    CurrencyDollarIcon,
    ChartBarIcon,
    ClockIcon,
    ArrowPathIcon
  },
  props: {
    events: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(event => 
          'date' in event && 
          'title' in event && 
          'description' in event
        );
      }
    }
  },
  setup(props) {
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    const getEventIcon = (eventType) => {
      const icons = {
        investment: CurrencyDollarIcon,
        performance: ChartBarIcon,
        lockin: ClockIcon,
        payout: ArrowPathIcon
      };
      return icons[eventType] || CurrencyDollarIcon;
    };

    const getIconBackground = (eventType) => {
      const backgrounds = {
        investment: 'bg-blue-500',
        performance: 'bg-green-500',
        lockin: 'bg-yellow-500',
        payout: 'bg-purple-500'
      };
      return backgrounds[eventType] || 'bg-gray-500';
    };

    const processedEvents = computed(() => {
      return props.events.map(event => ({
        ...event,
        icon: getEventIcon(event.type),
        iconBackground: getIconBackground(event.type)
      }));
    });

    return {
      events: processedEvents,
      formatDate
    };
  }
};
</script> 
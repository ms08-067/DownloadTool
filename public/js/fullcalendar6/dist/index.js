import { globalPlugins } from '@fullcalendar/core/internal.js';
import interactionPlugin__default from '@fullcalendar/interaction/index.js';
export * from '@fullcalendar/interaction/index.js';
import dayGridPlugin from '@fullcalendar/daygrid/index.js';
import timeGridPlugin from '@fullcalendar/timegrid/index.js';
import listPlugin from '@fullcalendar/list/index.js';
import scrollGridPlugin from '@fullcalendar/scrollgrid/index.js';
import adaptivePlugin from '@fullcalendar/adaptive/index.js';
import timelinePlugin from '@fullcalendar/timeline/index.js';
import resourcePlugin from '@fullcalendar/resource/index.js';
import resourceDayGridPlugin from '@fullcalendar/resource-daygrid/index.js';
import resourceTimeGridPlugin from '@fullcalendar/resource-timegrid/index.js';
import resourceTimelinePlugin from '@fullcalendar/resource-timeline/index.js';
export * from '@fullcalendar/core/index.js';

globalPlugins.push(interactionPlugin__default, dayGridPlugin, timeGridPlugin, listPlugin, scrollGridPlugin, adaptivePlugin, timelinePlugin, resourcePlugin, resourceDayGridPlugin, resourceTimeGridPlugin, resourceTimelinePlugin);

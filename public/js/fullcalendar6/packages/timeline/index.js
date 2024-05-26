import { createPlugin } from '@fullcalendar/core/index.js';
import premiumCommonPlugin from '@fullcalendar/premium-common/index.js';
import { TimelineView } from './internal.js';
import '@fullcalendar/scrollgrid/index.js';
import { injectStyles } from '@fullcalendar/core/internal.js';
import '@fullcalendar/core/preact.js';
import '@fullcalendar/scrollgrid/internal.js';

var css_248z = "\n\n  .fc .fc-timeline-body {\n    min-height: 100%;\n    position: relative;\n    z-index: 1; /* scope slots, bg, etc */\n  }\n/*\nvertical slots in both the header AND the body\n*/\n.fc .fc-timeline-slots {\n    position: absolute;\n    z-index: 1;\n    top: 0;\n    bottom: 0\n  }\n.fc .fc-timeline-slots > table {\n      height: 100%;\n    }\n.fc {\n\n  /* border for both header AND body cells */\n\n}\n.fc .fc-timeline-slot-minor {\n    border-style: dotted;\n  }\n.fc {\n\n  /* header cells (aka \"label\") */\n\n}\n.fc .fc-timeline-slot-frame {\n    display: flex;\n    align-items: center; /* vertical align */\n    justify-content: center; /* horizontal align */\n  }\n.fc .fc-timeline-header-row-chrono { /* a row of times */\n  }\n.fc .fc-timeline-header-row-chrono .fc-timeline-slot-frame {\n      justify-content: flex-start; /* horizontal align left or right */\n    }\n.fc .fc-timeline-header-row:last-child { /* guaranteed NOT to have sticky elements */\n  }\n.fc .fc-timeline-header-row:last-child .fc-timeline-slot-frame {\n      /* so text doesn't bleed out and cause extra scroll */\n      /* (won't work with sticky elements) */\n      overflow: hidden;\n    }\n.fc .fc-timeline-slot-cushion {\n    padding: 4px 5px; /* TODO: unify with fc-col-header? */\n    white-space: nowrap;\n  }\n.fc {\n\n  /* NOTE: how does the top row of cells get horizontally centered? */\n  /* for the non-chrono-row, the fc-sticky system looks for text-align center, */\n  /* and it's a fluke that the default browser stylesheet already does this for <th> */\n  /* TODO: have StickyScrolling look at natural left coord to detect centeredness. */\n\n}\n/* only owns one side, so can do dotted */\n.fc-direction-ltr .fc-timeline-slot { border-right: 0 !important }\n.fc-direction-rtl .fc-timeline-slot { border-left: 0 !important }\n.fc .fc-timeline-now-indicator-container {\n    position: absolute;\n    z-index: 4;\n    top: 0;\n    bottom: 0;\n    left: 0;\n    right: 0;\n    width: 0;\n  }\n.fc .fc-timeline-now-indicator-arrow,\n  .fc .fc-timeline-now-indicator-line {\n    position: absolute;\n    top: 0;\n    border-style: solid;\n    border-color: var(--fc-now-indicator-color);\n  }\n.fc .fc-timeline-now-indicator-arrow {\n    margin: 0 -6px; /* 5, then one more to counteract scroller's negative margins */\n\n    /* triangle pointing down. TODO: mixin */\n    border-width: 6px 5px 0 5px;\n    border-left-color: transparent;\n    border-right-color: transparent;\n  }\n.fc .fc-timeline-now-indicator-line {\n    margin: 0 -1px; /* counteract scroller's negative margins */\n    bottom: 0;\n    border-width: 0 0 0 1px;\n  }\n.fc {\n\n  /* container */\n\n}\n.fc .fc-timeline-events {\n    position: relative;\n    z-index: 3;\n    width: 0; /* for event positioning. will end up on correct side based on dir */\n  }\n.fc {\n\n  /* harness */\n\n}\n.fc .fc-timeline-event-harness,\n  .fc .fc-timeline-more-link {\n    position: absolute;\n    top: 0; /* for when when top can't be computed yet */\n    /* JS will set tht left/right */\n  }\n/* z-index, scoped within fc-timeline-events */\n.fc-timeline-event { z-index: 1 }\n.fc-timeline-event.fc-event-mirror { z-index: 2 }\n.fc-timeline-event {\n  position: relative; /* contains things. TODO: make part of fc-h-event and fc-v-event */\n  display: flex; /* for v-aligning start/end arrows and making fc-event-main stretch all the way */\n  align-items: center;\n  border-radius: 0;\n  padding: 2px 1px;\n  margin-bottom: 1px;\n  font-size: var(--fc-small-font-size)\n\n  /* time and title spacing */\n  /* ---------------------------------------------------------------------------------------------------- */\n}\n.fc-timeline-event .fc-event-main {\n    flex-grow: 1;\n    flex-shrink: 1;\n    min-width: 0; /* important for allowing to shrink all the way */\n  }\n.fc-timeline-event .fc-event-time {\n    font-weight: bold;\n  }\n.fc-timeline-event .fc-event-time,\n  .fc-timeline-event .fc-event-title {\n    white-space: nowrap;\n    padding: 0 2px;\n  }\n/* move 1px away from slot line */\n.fc-direction-ltr .fc-timeline-event.fc-event-end,\n  .fc-direction-ltr .fc-timeline-more-link {\n    margin-right: 1px;\n  }\n.fc-direction-rtl .fc-timeline-event.fc-event-end,\n  .fc-direction-rtl .fc-timeline-more-link {\n    margin-left: 1px;\n  }\n/* make event beefier when overlap not allowed */\n.fc-timeline-overlap-disabled .fc-timeline-event {\n  padding-top: 5px;\n  padding-bottom: 5px;\n  margin-bottom: 0;\n}\n/* arrows indicating the event continues into past/future */\n/* ---------------------------------------------------------------------------------------------------- */\n/* part of the flexbox flow */\n.fc-timeline-event:not(.fc-event-start):before,\n.fc-timeline-event:not(.fc-event-end):after {\n  content: \"\";\n  flex-grow: 0;\n  flex-shrink: 0;\n  opacity: .5;\n\n  /* triangle. TODO: mixin */\n  width: 0;\n  height: 0;\n  margin: 0 1px;\n  border: 5px solid #000; /* TODO: var */\n  border-top-color: transparent;\n  border-bottom-color: transparent;\n}\n/* pointing left */\n.fc-direction-ltr .fc-timeline-event:not(.fc-event-start):before,\n.fc-direction-rtl .fc-timeline-event:not(.fc-event-end):after {\n  border-left: 0;\n}\n/* pointing right */\n.fc-direction-ltr .fc-timeline-event:not(.fc-event-end):after,\n.fc-direction-rtl .fc-timeline-event:not(.fc-event-start):before {\n  border-right: 0;\n}\n/* +more events indicator */\n/* ---------------------------------------------------------------------------------------------------- */\n.fc-timeline-more-link {\n  font-size: var(--fc-small-font-size);\n  color: var(--fc-more-link-text-color);\n  background: var(--fc-more-link-bg-color);\n  padding: 1px;\n  cursor: pointer;\n}\n.fc-timeline-more-link-inner { /* has fc-sticky */\n  display: inline-block;\n  left: 0;\n  right: 0;\n  padding: 2px;\n}\n.fc .fc-timeline-bg { /* a container for bg content */\n    position: absolute;\n    z-index: 2;\n    top: 0;\n    bottom: 0;\n    width: 0;\n    left: 0; /* will take precedence when LTR */\n    right: 0; /* will take precedence when RTL */ /* TODO: kill */\n  }\n.fc .fc-timeline-bg .fc-non-business { z-index: 1 }\n.fc .fc-timeline-bg .fc-bg-event { z-index: 2 }\n.fc .fc-timeline-bg .fc-highlight { z-index: 3 }\n.fc .fc-timeline-bg-harness {\n    position: absolute;\n    top: 0;\n    bottom: 0;\n  }\n\n";
injectStyles(css_248z);

var index = createPlugin({
    name: '@fullcalendar/timeline',
    premiumReleaseDate: '2022-11-22',
    deps: [premiumCommonPlugin],
    initialView: 'timelineDay',
    views: {
        timeline: {
            component: TimelineView,
            usesMinMaxTime: true,
            eventResizableFromStart: true, // how is this consumed for TimelineView tho?
        },
        timelineDay: {
            type: 'timeline',
            duration: { days: 1 },
        },
        timelineWeek: {
            type: 'timeline',
            duration: { weeks: 1 },
        },
        timelineMonth: {
            type: 'timeline',
            duration: { months: 1 },
        },
        timelineYear: {
            type: 'timeline',
            duration: { years: 1 },
        },
    },
});

export { index as default };

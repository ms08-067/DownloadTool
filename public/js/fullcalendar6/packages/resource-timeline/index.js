import { createPlugin } from '@fullcalendar/core/index.js';
import premiumCommonPlugin from '@fullcalendar/premium-common/index.js';
import timelinePlugin from '@fullcalendar/timeline/index.js';
import resourcePlugin from '@fullcalendar/resource/index.js';
import { ResourceTimelineView } from './internal.js';
import '@fullcalendar/scrollgrid/index.js';
import { injectStyles } from '@fullcalendar/core/internal.js';
import '@fullcalendar/core/preact.js';
import '@fullcalendar/timeline/internal.js';
import '@fullcalendar/resource/internal.js';
import '@fullcalendar/scrollgrid/internal.js';

var css_248z = "\n\n  .fc .fc-resource-timeline-divider {\n    width: 3px; /* important to have width to shrink this cell. no cross-browser problems */\n    cursor: col-resize;\n  }\n\n.fc .fc-resource-group {\n    /* make it look less like a <th> */\n    font-weight: inherit;\n    text-align: inherit;\n  }\n\n.fc {\n\n\n  /* will match horizontal groups in the datagrid AND group lanes in the timeline area */\n\n}\n\n.fc .fc-resource-timeline .fc-resource-group:not([rowspan]) {\n      background: var(--fc-neutral-bg-color);\n    }\n\n.fc .fc-timeline-lane-frame {\n    position: relative; /* contains the fc-timeline-bg container, which liquidly expands */\n    /* the height is explicitly set by row-height-sync */\n  }\n\n.fc .fc-timeline-overlap-enabled .fc-timeline-lane-frame .fc-timeline-events { /* has height set on it */\n    box-sizing: content-box; /* padding no longer part of height */\n    padding-bottom: 10px; /* give extra spacing underneath for selecting */\n  }\n\n/* hack to make bg expand to lane's full height in resource-timeline with expandRows (#6134) */\n.fc-timeline-body-expandrows td.fc-timeline-lane {\n    position: relative;\n  }\n.fc-timeline-body-expandrows .fc-timeline-lane-frame {\n    position: static;\n  }\n/* the \"frame\" */\n.fc-datagrid-cell-frame-liquid {\n  height: 100%; /* needs liquid hack */\n}\n.fc-liquid-hack .fc-datagrid-cell-frame-liquid {\n  height: auto;\n  position: absolute;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  }\n.fc {\n\n  /* the \"frame\" in a HEADER */\n  /* needs to position the column resizer */\n  /* needs to vertically center content */\n\n}\n.fc .fc-datagrid-header .fc-datagrid-cell-frame {\n      position: relative; /* for resizer */\n      display: flex;\n      justify-content: flex-start; /* horizontal align (natural left/right) */\n      align-items: center; /* vertical align */\n    }\n.fc {\n\n  /* the column resizer (only in HEADER) */\n\n}\n.fc .fc-datagrid-cell-resizer {\n    position: absolute;\n    z-index: 1;\n    top: 0;\n    bottom: 0;\n    width: 5px;\n    cursor: col-resize;\n  }\n.fc {\n\n  /* the cushion */\n\n}\n.fc .fc-datagrid-cell-cushion {\n    padding: 8px;\n    white-space: nowrap;\n    overflow: hidden; /* problem for col resizer :( */\n  }\n.fc {\n\n  /* expander icons */\n\n}\n.fc .fc-datagrid-expander {\n    cursor: pointer;\n    opacity: 0.65\n\n  }\n.fc .fc-datagrid-expander .fc-icon { /* the expander and spacers before the expander */\n      display: inline-block;\n      width: 1em; /* ensure constant width, esp for empty icons */\n    }\n.fc .fc-datagrid-expander-placeholder {\n    cursor: auto;\n  }\n.fc .fc-resource-timeline-flat .fc-datagrid-expander-placeholder {\n      display: none;\n    }\n.fc-direction-ltr .fc-datagrid-cell-resizer { right: -3px }\n.fc-direction-rtl .fc-datagrid-cell-resizer { left: -3px }\n.fc-direction-ltr .fc-datagrid-expander { margin-right: 3px }\n.fc-direction-rtl .fc-datagrid-expander { margin-left: 3px }\n";
injectStyles(css_248z);

var index = createPlugin({
    name: '@fullcalendar/resource-timeline',
    premiumReleaseDate: '2022-11-22',
    deps: [
        premiumCommonPlugin,
        resourcePlugin,
        timelinePlugin,
    ],
    initialView: 'resourceTimelineDay',
    views: {
        resourceTimeline: {
            type: 'timeline',
            component: ResourceTimelineView,
            needsResourceData: true,
            resourceAreaWidth: '30%',
            resourcesInitiallyExpanded: true,
            eventResizableFromStart: true, // TODO: not DRY with this same setting in the main timeline config
        },
        resourceTimelineDay: {
            type: 'resourceTimeline',
            duration: { days: 1 },
        },
        resourceTimelineWeek: {
            type: 'resourceTimeline',
            duration: { weeks: 1 },
        },
        resourceTimelineMonth: {
            type: 'resourceTimeline',
            duration: { months: 1 },
        },
        resourceTimelineYear: {
            type: 'resourceTimeline',
            duration: { years: 1 },
        },
    },
});

export { index as default };

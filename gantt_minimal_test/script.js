gantt.config.date_format = "%Y-%m-%d";
gantt.config.xml_date = "%Y-%m-%d";

gantt.config.lightbox.sections = [
    {name: "description", height: 70, map_to: "text", type: "textarea", focus: true},
    {name: "time", type: "duration", map_to: "auto"},
    {name: "my_custom_section", map_to: "my_data", type: "text", label: "Mi Sección Personalizada"}
];

gantt.init("gantt_here");

gantt.parse({
    data: [
        {id: 1, text: "Task #1", start_date: "2025-09-15", duration: 3, progress: 0.6, open: true, my_data: "Hello Custom!"},
        {id: 2, text: "Task #2", start_date: "2025-09-18", duration: 3, progress: 0.4, open: true, my_data: "Another value"}
    ],
    links: []
});
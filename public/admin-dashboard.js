(() => {
    const clock = document.querySelector("[data-clock]");
    const clockDate = document.querySelector("[data-clock-date]");
    const calendar = document.querySelector("[data-calendar]");
    const calendarTitle = document.querySelector("[data-calendar-title]");

    const updateClock = () => {
        const now = new Date();
        if (clock) {
            clock.textContent = new Intl.DateTimeFormat("id-ID", {
                timeZone: "Asia/Jakarta",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: false
            }).format(now);
        }
        if (clockDate) {
            clockDate.textContent = new Intl.DateTimeFormat("id-ID", {
                timeZone: "Asia/Jakarta",
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric"
            }).format(now);
        }
    };

    const renderCalendar = () => {
        if (!calendar || !calendarTitle) return;

        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const monthName = new Intl.DateTimeFormat("id-ID", { month: "long", year: "numeric" }).format(now);
        const weekdays = ["Mg", "Sn", "Sl", "Rb", "Km", "Jm", "Sb"];

        calendarTitle.textContent = monthName;
        calendar.innerHTML = weekdays.map((day) => `<span class="calendar-weekday">${day}</span>`).join("");
        for (let index = 0; index < firstDay; index += 1) {
            calendar.insertAdjacentHTML("beforeend", '<span class="calendar-day is-empty" aria-hidden="true"></span>');
        }
        for (let day = 1; day <= daysInMonth; day += 1) {
            const isToday = day === now.getDate();
            calendar.insertAdjacentHTML("beforeend", `<span class="calendar-day${isToday ? " is-today" : ""}"${isToday ? ' aria-current="date"' : ""}>${day}</span>`);
        }
    };

    updateClock();
    window.setInterval(updateClock, 1000);
    renderCalendar();
})();

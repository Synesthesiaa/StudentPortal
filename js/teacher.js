// Sidebar functionality
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

function toggleSidebar() {
    if (window.innerWidth < 1280) { // Only work on screens below 1280px (xl breakpoint)
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('hidden');
        overlay.classList.toggle('hidden');
    }
}

// Only add event listeners if elements exist
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', toggleSidebar);
}

if (overlay) {
    overlay.addEventListener('click', toggleSidebar);
}

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1280) { // xl breakpoint (1280px)
        // Hide mobile sidebar and show desktop sidebar
        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.add('hidden');
        }
        if (overlay) {
            overlay.classList.add('hidden');
        }
    } else {
        // Ensure mobile sidebar is hidden by default
        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.add('hidden');
        }
    }
});

// Close sidebar when clicking outside on mobile/tablet
document.addEventListener('click', function(e) {
    if (window.innerWidth < 1280) { // xl breakpoint
        if (sidebar && overlay && sidebarToggle) {
            if (!sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) && 
                !sidebar.classList.contains('hidden')) {
                toggleSidebar();
            }
        }
    }
});

// Initialize sidebar state on page load
window.addEventListener('load', function() {
    if (window.innerWidth < 1280) {
        // Ensure mobile sidebar is hidden
        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.add('hidden');
        }
    }
});

// Calendar functionality
let selectedDate = null;
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

function renderCalendar(targetId, month, year, highlightDay) {
    const target = document.getElementById(targetId);
    if (!target) return;
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    let html = '';
    
    // Add month/year navigation for full calendar
    if (targetId === 'calendar-full') {
        html += `
            <div class="flex justify-between items-center mb-4">
                <button onclick="changeMonth(${year}, ${month}, -1)" class="text-green-600 hover:text-green-600">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="flex items-center gap-2">
                    <select onchange="changeMonth(${year}, this.value)" class="text-green-600 font-semibold border-none focus:ring-0">
                        ${months.map((m, i) => `<option value="${i}" ${i === month ? 'selected' : ''}>${m}</option>`).join('')}
                    </select>
                    <select onchange="changeYear(this.value, ${month})" class="text-green-600 font-semibold border-none focus:ring-0">
                        ${Array.from({length: 10}, (_, i) => year - 5 + i).map(y => 
                            `<option value="${y}" ${y === year ? 'selected' : ''}>${y}</option>`
                        ).join('')}
                    </select>
                </div>
                <button onclick="changeMonth(${year}, ${month}, 1)" class="text-green-600 hover:text-green-600">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
    }

    html += '<table style="width:100%; text-align:center; border-collapse:collapse; font-size: 12px;">';
    html += '<tr>' + daysOfWeek.map(d=>`<th style='color:#4CAF50; font-size:11px; padding:4px 2px;'>${d}</th>`).join('') + '</tr>';
    let d = 1;
    for(let i=0; i<6; i++) {
        html += '<tr>';
        for(let j=0; j<7; j++) {
            if(i===0 && j<firstDay.getDay()) {
                html += '<td style="padding:4px 2px;"></td>';
            } else if(d > lastDay.getDate()) {
                html += '<td style="padding:4px 2px;"></td>';
            } else {
                let isToday = (year === today.getFullYear() && month === today.getMonth() && d === today.getDate());
                let isSelected = selectedDate && selectedDate.getDate() === d && selectedDate.getMonth() === month && selectedDate.getFullYear() === year;
                let bgColor = isSelected ? '#4CAF50' : (isToday ? '#e8f5e9' : 'none');
                let textColor = isSelected || isToday ? '#fff' : '#222';
                let fontWeight = isSelected || isToday ? 'bold' : 'normal';
                
                html += `<td style="padding:4px 2px; cursor:pointer; border-radius:50%; background:${bgColor}; color:${textColor}; font-weight:${fontWeight};" 
                    onclick="selectDate(${year}, ${month}, ${d}, '${targetId}')">${d}</td>`;
                d++;
            }
        }
        html += '</tr>';
        if(d > lastDay.getDate()) break;
    }
    html += '</table>';
    target.innerHTML = html;
}

function changeMonth(year, month, direction) {
    if (direction) {
        // Handle prev/next month navigation
        month += direction;
        if (month > 11) {
            month = 0;
            year++;
        } else if (month < 0) {
            month = 11;
            year--;
        }
    }
    currentMonth = month;
    currentYear = year;
    renderCalendar('calendar-full', month, year);
}

function changeYear(year, month) {
    currentYear = parseInt(year);
    renderCalendar('calendar-full', month, currentYear);
}

function selectDate(year, month, day, calendarId) {
    selectedDate = new Date(year, month, day);
    // Re-render both mini and full calendars to update the selection
    renderCalendar('calendar-mini', currentMonth, currentYear);
    if (calendarId === 'calendar-full') {
        renderCalendar('calendar-full', month, year);
    }
}

function updateDateTime() {
    const dt = new Date();
    const element = document.getElementById('calendar-datetime');
    if (element) {
        element.textContent = dt.toLocaleString();
    }
}

// Initialize calendar
const now = new Date();
currentMonth = now.getMonth();
currentYear = now.getFullYear();
renderCalendar('calendar-mini', currentMonth, currentYear);
setInterval(updateDateTime, 1000);
updateDateTime();

// Calendar modal functionality
const expandBtn = document.getElementById('expandCalendarBtn');
const modal = document.getElementById('calendar-modal');
const closeBtn = document.getElementById('closeCalendarBtn');

if (expandBtn) {
    expandBtn.addEventListener('click', function() {
        renderCalendar('calendar-full', currentMonth, currentYear);
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    });
}

if (closeBtn) {
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    });
}

if (modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    });
}

// Profile Dialog functionality
function openProfileDialog() {
    document.getElementById('profileDialog').classList.remove('hidden');
    document.getElementById('profileDialog').classList.add('flex');
}

function closeProfileDialog() {
    document.getElementById('profileDialog').classList.add('hidden');
    document.getElementById('profileDialog').classList.remove('flex');
}

// Edit Profile Dialog functionality
function openEditProfileDialog() {
    document.getElementById('editProfileDialog').classList.remove('hidden');
    document.getElementById('editProfileDialog').classList.add('flex');
    document.getElementById('profileDialog').classList.add('hidden');
    document.getElementById('profileDialog').classList.remove('flex');
}

function closeEditProfileDialog() {
    document.getElementById('editProfileDialog').classList.add('hidden');
    document.getElementById('editProfileDialog').classList.remove('flex');
}

// Image Preview functionality
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewAvatar').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}


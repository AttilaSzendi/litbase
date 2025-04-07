<template>
    <div>
        <FullCalendar :options="calendarOptions" @eventClick="editTask"/>

        <TaskModal
            ref="taskModal"
            :show="showModal"
            :users="users"
            :overloadErrors="overloadErrors"
            :isEditMode="isEditMode"
            :selectedTask="selectedTask"
            @deleteTask="deleteTask"
            @save="submitTask"
            @close="closeModal"
            @splitTask="splitTask"
            @forceInsertTask="forceInsertTask"
        />
    </div>
</template>

<script>
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import TaskModal from "./TaskModal.vue"
import axios from "axios";

export default {
    components: {
        FullCalendar,
        TaskModal,
    },
    data() {
        return {
            selectedDate: null,
            isEditMode: false,
            selectedTask: null,
            overloadErrors: [],
            events: [],
            showModal: false,
            users: [],
            calendarOptions: {
                plugins: [dayGridPlugin, interactionPlugin],
                initialView: "dayGridWeek",
                firstDay: 1,
                datesSet: (range) => this.viewChanged(range),
                eventClick: (event) => this.editTask(event),
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridWeek",
                },
                events: [],
                dateClick: this.handleDateClick,
            },
        };
    },
    methods: {
        async deleteTask() {
            try {
                const confirmation = window.confirm("Biztosan törölni szeretnéd ezt a feladatot?");
                if (confirmation) {
                    const response = await axios.delete(`/api/tasks/${this.selectedTask.id}`);
                    if (response.status === 200) {
                        await this.fetchTasks(new Date().toISOString().slice(0, 10));
                        this.$refs.taskModal.resetNewTask();
                        this.closeModal();
                    }
                }
            } catch (error) {
                console.error("Hiba történt a törlés közben:", error);
                alert("Hiba történt a törlés során. Kérlek próbáld újra!");
            }
        },

        editTask(clickInfo) {
            this.selectedDate = clickInfo.event.startStr;
            this.selectedTask = {...clickInfo.event.extendedProps};
            this.selectedTask.name = clickInfo.event.title;
            this.isEditMode = true;
            this.openModal();
        },

        async fetchTasks(date) {
            try {
                const response = await axios.get("/api/tasks", {params: {date}});

                this.events = Object.entries(response.data.data).flatMap(([date, tasks]) =>
                    tasks.map(task => ({
                        title: task.name,
                        start: date,
                        extendedProps: {
                            id: task.id,
                            estimatedMinutes: task.estimatedMinutes,
                            priority: task.priorityId,
                            assignedUserIds: (task.assignedUsers || []).map(user => user.id),
                            scheduledDay: task.scheduledDay.split('T')[0]
                        },
                    }))
                );

                this.calendarOptions.events = this.events;
            } catch (error) {
                console.error("Hiba a feladatok lekérésekor:", error);
            }
        },

        handleDateClick(info) {
            this.selectedDate = info.dateStr;
            this.openModal();
        },

        openModal() {
            this.showModal = true;
            this.fetchUsers();
        },

        closeModal() {
            this.isEditMode = false;
            this.showModal = false;
            this.overloadErrors = [];
            this.selectedTask = null;
        },

        async fetchUsers() {
            try {
                const response = await axios.get("/api/users");
                this.users = response.data.data;
            } catch (error) {
                console.error("Hiba a felhasználók lekérésekor:", error);
            }
        },

        async submitTask(task) {
            task.scheduled_day = task.scheduled_day ?? this.selectedDate;

            try {
                if (this.isEditMode) {
                    await axios.patch(`/api/tasks/${this.selectedTask.id}`, task);
                } else {
                    await axios.post("/api/tasks", task);
                }

                await this.fetchTasks(new Date().toISOString().slice(0, 10));
                this.$refs.taskModal.resetNewTask();
                this.closeModal();
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const validationErrors = error.response.data.errors.assignee_overload || [];
                    this.handleOverloadError(validationErrors);
                } else {
                    console.error("Hiba a feladat mentésekor:", error);
                }
            }
        },

        handleOverloadError(errors) {
            if (errors.length > 0) {
                this.showModal = true;
                this.overloadErrors = errors;
            }
        },

        async forceInsertTask() {
            // we need to keep in mind that we might have problems executing this. Ex.: what if the lower priority task
            // is 8 hours long and we want to insert a 9 hours task. we cannot swap those out so easily. The 9 hours task have to be split.
        },

        async splitTask(newTask, remainingMinutes) {
            let task1 = {
                "name": newTask.name,
                "estimated_minutes": remainingMinutes,
                "priority_id": newTask.priorityId,
                "assigned_user_ids": newTask.assignedUserIds,
            }

            let task2 = {
                "name": newTask.name + ' (2nd part)',
                "estimated_minutes": newTask.estimatedMinutes - remainingMinutes,
                "priority_id": newTask.priorityId,
                "assigned_user_ids": newTask.assignedUserIds,
                "scheduled_day": this.getNextWeekday(),
            }

            await this.submitTask(task1);
            await this.submitTask(task2);
        },

        getNextWeekday() {
            const nextDate = new Date(this.selectedDate);
            let day = nextDate.getDay();

            nextDate.setDate(nextDate.getDate() + (day >= 5 ? 8 - day : 1));

            return nextDate.toISOString().split('T')[0];
        },
        viewChanged(range) {
            const startDate = range.startStr.split('T')[0];

            this.fetchTasks(startDate);
        },
    },
};
</script>

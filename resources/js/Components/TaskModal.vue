<template>
    <div v-if="show" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2>{{ title }}</h2>

                <button
                    type="button"
                    class="btn btn-danger"
                    @click="deleteTask"
                >
                    Törlés
                </button>

                <span class="close" @click="close">&times;</span>
            </div>

            <div v-if="overloadErrors.length > 0" class="error-container">
                <div v-if="hasMultipleAssignee">nem lehet a taskot felbontani, ha több userhez tartozik!</div>
                <div v-else>
                    <p v-for="(error, index) in overloadErrors" :key="index">
                        {{ error.message }} (Maradék idő: {{ error.remainingMinutes }} perc)
                    </p>

                    <div class="error-actions">
                        <button
                            @click="handleSplitTask"
                            class="split-btn"
                            :disabled="isUnsplitable">
                            Feladat felbontása
                        </button>
                        <button @click="handleForceInsert" class="force-btn" :disabled="isForceInsertable">Beszúrás mégis</button>
                        <button @click="close" class="cancel-btn">Mégse</button>
                    </div>
                </div>
            </div>

            <form v-else @submit.prevent="submit" class="modal-form">
                <div class="form-group">
                    <label for="taskName">Feladat neve:</label>
                    <input type="text" v-model="newTask.name" required placeholder="Feladat neve" />
                </div>

                <div class="form-group">
                    <label for="estimatedMinutes">Idő (perc):</label>
                    <input type="number" v-model="newTask.estimatedMinutes" required placeholder="Például: 120" />
                </div>

                <div class="form-group">
                    <label for="assignedUserIds">Felhasználók:</label>
                    <select v-model="newTask.assignedUserIds" multiple>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Prioritás:</label>
                    <select v-model="newTask.priorityId" id="priority">
                        <option v-for="priority in priorities" :key="priority.value" :value="priority.value">
                            {{ priority.label }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Dátum:</label>
                    <input
                        type="date"
                        id="date"
                        v-model="newTask.scheduledDay"
                    />
                </div>

                <div class="modal-footer">
                    <button type="submit" class="submit-btn">{{ isEditMode ? 'Mentés' : 'Hozzáadás' }}</button>
                    <button type="button" @click="close" class="cancel-btn">Mégse</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isEditMode: Boolean,
        show: Boolean,
        users: Array,
        overloadErrors: Array,
        selectedTask: Object
    },
    data() {
        return {
            priorities: [
                { value: 1, label: "Alacsony" },
                { value: 2, label: "Közepes" },
                { value: 3, label: "Magas" }
            ],
            newTask: {
                name: "",
                estimatedMinutes: "",
                assignedUserIds: [],
                priorityId: 1,
                scheduledDay: "",
            },
        };
    },
    methods: {
        deleteTask() {
            this.$emit("deleteTask");
        },
        submit() {
            this.$emit("save", {
                name: this.newTask.name,
                estimated_minutes: this.newTask.estimatedMinutes,
                assigned_user_ids: this.newTask.assignedUserIds,
                priority_id: this.newTask.priorityId,
                scheduled_day: this.newTask.scheduledDay,
            });
        },
        close() {
            this.$emit("close");
            this.resetNewTask();
        },
        resetNewTask() {
            this.newTask = {name: "", estimatedMinutes: "", assignedUserIds: [], priorityId: 1};
        },
        handleSplitTask() {
            this.$emit("split-task", this.newTask, this.overloadErrors[0].remainingMinutes);
        },
        handleForceInsert() {
            this.$emit("force-insert-task", this.newTask);
        },
    },
    computed: {
        isUnsplitable() {
            return this.overloadErrors.some(error => error.remainingMinutes === 0)
        },

        async isForceInsertable() {
            return this.overloadErrors.some(error => error.remainingMinutes === 0)
        },

        hasMultipleAssignee() {
            return this.newTask.assignedUserIds.length > 1
        },
        title() {
            return this.isEditMode ? 'Feladat szerkesztése' : 'Új feladat hozzáadása';
        }
    },
    watch: {
        selectedTask: {
            immediate: true,
            handler(newTask) {
                console.log(newTask)
                if (this.isEditMode && newTask) {
                    this.newTask = {
                        name: newTask.name || "",
                        estimatedMinutes: newTask.estimatedMinutes || "",
                        assignedUserIds: newTask.assignedUserIds || [],
                        priorityId: newTask.priority || 1,
                        scheduledDay: newTask.scheduledDay || "",
                    };
                }
            }
        }
    },
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-container {
    background-color: #fff;
    border-radius: 8px;
    width: 500px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

.modal-header .close {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    cursor: pointer;
}

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-height: 80vh;
    overflow-y: auto;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 14px;
    color: #555;
}

.form-group input,
.form-group select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    width: 100%;
}

.modal-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

button {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}

.submit-btn {
    background-color: #4CAF50;
    color: white;
}

.submit-btn:hover {
    background-color: #45a049;
}

.cancel-btn, .btn-danger {
    background-color: #f44336;
    color: white;
}

.cancel-btn:hover {
    background-color: #e53935;
}

.error-container {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.error-container p {
    margin: 0;
    font-size: 14px;
}

.error-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.split-btn {
    background-color: #f0ad4e;
    color: white;
}

.split-btn:hover {
    background-color: #ec971f;
}

.force-btn {
    background-color: #007bff;
    color: white;
}

.force-btn:hover {
    background-color: #0056b3;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
    opacity: 0.6;
}

.form-group select[multiple] {
    height: 100px;
    overflow-y: auto;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-container {
    animation: fadeIn 0.3s ease-in-out;
}
</style>

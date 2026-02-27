export default function chatPage({ rooms, directUsers, authUser }) {
    return {
        rooms,
        directUsers,
        messages: [],
        draft: "",
        activeTarget: null,
        activeLabel: "Select a room or DM",
        sending: false,
        lastFetchedAt: null,
        pollingLabel: "",
        statusText: "",
        init() {
            if (this.rooms.length > 0) {
                this.selectTarget("room", this.rooms[0].id, this.rooms[0].name);
            } else if (this.directUsers.length > 0) {
                this.selectTarget(
                    "user",
                    this.directUsers[0].id,
                    this.directUsers[0].name,
                );
            }
            setInterval(() => this.poll(), 5000);
        },
        selectTarget(type, id, label) {
            this.activeTarget = { type, id };
            this.activeLabel = label;
            this.messages = [];
            this.lastFetchedAt = null;
            this.fetchMessages(true);
        },
        async fetchMessages(fullHistory = false) {
            if (!this.activeTarget) return;
            try {
                const params = {
                    target_type: this.activeTarget.type,
                    target_id: this.activeTarget.id,
                };
                if (!fullHistory && this.lastFetchedAt) {
                    params.since = this.lastFetchedAt;
                }
                const { data } = await axios.get("/chat/messages", { params });
                const incoming = data.data || [];
                if (fullHistory) {
                    this.messages = incoming;
                } else if (incoming.length > 0) {
                    this.messages = [...this.messages, ...incoming];
                }
                if (incoming.length > 0) {
                    this.lastFetchedAt =
                        incoming[incoming.length - 1].created_at;
                    this.$nextTick(() => {
                        const list = document.getElementById("message-list");
                        if (list) {
                            list.scrollTop = list.scrollHeight;
                        }
                    });
                }
                this.pollingLabel = `Updated ${new Date().toLocaleTimeString()}`;
            } catch (error) {
                console.error(error);
                this.statusText = "Unable to load messages";
            }
        },
        async sendMessage() {
            if (!this.draft.trim() || !this.activeTarget) return;
            this.sending = true;
            try {
                const { data } = await axios.post("/chat/messages", {
                    body: this.draft,
                    target_type: this.activeTarget.type,
                    target_id: this.activeTarget.id,
                });
                this.messages.push(data.data);
                this.lastFetchedAt = data.data.created_at;
                this.draft = "";
                this.statusText = "Sent";
                this.$nextTick(() => {
                    const list = document.getElementById("message-list");
                    if (list) {
                        list.scrollTop = list.scrollHeight;
                    }
                });
            } catch (error) {
                console.error(error);
                this.statusText = "Failed to send";
            } finally {
                this.sending = false;
            }
        },
        poll() {
            this.fetchMessages(false);
        },
        formatTime(timestamp) {
            if (!timestamp) return "";
            return new Date(timestamp).toLocaleString();
        },
    };
}

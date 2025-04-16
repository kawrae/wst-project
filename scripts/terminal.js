document.addEventListener("DOMContentLoaded", () => {
    const terminal = document.getElementById("terminal");
    const audio = document.getElementById("boot-sound");
    const cursor = "<span class='cursor'>■</span>";
    const commandHistory = [];
    let historyIndex = -1;

    const allCommands = [
        "help", "page", "add", "delete", "edit", "list", "clear", "logout"
    ];

    const bootLines = [
        `> User detected: ${userName}`,
        `> Role: ${userRole}`,
        `> Status: LIVE`
    ];

    const welcomeLines = [
        "",
        `Welcome ${userName}!`,
        "Enter 'help' for list of commands.",
        ""
    ];

    let i = 0, j = 0;
    let currentLine = "";
    let outputLines = [];

    function renderTerminal(input = "") {
        terminal.innerHTML =
            outputLines.join("<br>") +
            (currentLine ? `<br>${currentLine}` : "") +
            `<br>> ${input}${cursor}`;
    
        requestAnimationFrame(() => {
            terminal.scrollTop = Math.floor(terminal.scrollHeight / 24) * 24;
        });
    }

    function parseArguments(input) {
        const regex = /"([^"]+)"|\S+/g;
        const matches = [];
        let match;
        while ((match = regex.exec(input)) !== null) {
            matches.push(match[1] || match[0]);
        }
    
        if (input.includes("add user") && matches.length < 7) {
            outputLines.push("⚠️ Please ensure all fields are wrapped in double quotes!");
        }
    
        return matches;
    }
    

    function suggestCommand(input) {
        const closest = allCommands.find(cmd => cmd.startsWith(input));
        return closest ? `Did you mean '${closest}'?` : "";
    }

    function renderHelp(role) {
        outputLines.push("> Available commands:");
        outputLines.push('- page "products/profile"');
        if (role === "admin" || role === "owner") {
            outputLines.push('- add user "id" "name" "email" "password" "user_type"');
            outputLines.push('- delete user "name" "email"');
            outputLines.push('- add product "id" "description" "price" "image"');
            outputLines.push('- edit product "id" "description" "price" "image"');
            outputLines.push('- delete product "name"');
            outputLines.push('- list users');
        }
        outputLines.push('- list products');
        outputLines.push('- clear');
        outputLines.push('- logout');
    }

    function enableCommandInput() {
        let input = "";
        renderTerminal(input);

        document.addEventListener("keydown", function handleKey(e) {
            if (e.key === "Backspace") {
                input = input.slice(0, -1);
            } else if (e.key === "ArrowUp") {
                if (commandHistory.length > 0 && historyIndex > 0) {
                    historyIndex--;
                    input = commandHistory[historyIndex];
                }
            } else if (e.key === "ArrowDown") {
                if (commandHistory.length > 0 && historyIndex < commandHistory.length - 1) {
                    historyIndex++;
                    input = commandHistory[historyIndex];
                } else {
                    input = "";
                }
            } else if (e.key === "Enter") {
                const cmd = input.trim();
                if (cmd.length > 0) {
                    commandHistory.push(cmd);
                    historyIndex = commandHistory.length;
                }

                outputLines.push(`> ${cmd}`);
                const args = parseArguments(cmd);
                const mainCmd = args[0]?.toLowerCase();
                const role = userRole.toLowerCase();

                switch (mainCmd) {
                    case "help":
                        renderHelp(role);
                        break;

                    case "page":
                        if (["profile", "products"].includes(args[1]?.toLowerCase())) {
                            window.location.href = `${args[1].toLowerCase()}.php`;
                            return;
                        } else {
                            outputLines.push('Usage: page "profile"');
                        }
                        break;

                    case "add":
                        if (args[1] === "user" && (role === "admin" || role === "owner")) {
                            const [_, __, id, name, email, password, userType] = args;

                            if (!id || !name || !email || !password || !userType) {
                                outputLines.push('Usage: add user "id" "name" "email" "password" "user_type"');
                            } else {
                                fetch("terminal_api.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        action: "add_user",
                                        id, name, email, password,
                                        user_type: userType
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    outputLines.push(data.message);
                                    renderTerminal();
                                });
                            }
                        } else if (args[1] === "product" && (role === "admin" || role === "owner")) {
                            const [_, __, id, desc, price, img] = args;
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({
                                    action: "add_product", id, description: desc, price: parseFloat(price), image: img
                                })
                            }).then(res => res.json()).then(data => {
                                outputLines.push(data.message);
                                renderTerminal();
                            });
                        } else {
                            outputLines.push("Invalid add command.");
                        }
                        break;

                    case "edit":
                        if (args[1] === "product" && (role === "admin" || role === "owner")) {
                            const [_, __, id, desc, price, img] = args;
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({
                                    action: "edit_product", id, description: desc, price: parseFloat(price), image: img
                                })
                            }).then(res => res.json()).then(data => {
                                outputLines.push(data.message);
                                renderTerminal();
                            });
                        } else {
                            outputLines.push("Invalid edit command.");
                        }
                        break;

                    case "delete":
                        if (args[1] === "user" && (role === "admin" || role === "owner")) {
                            const name = args[2], email = args[3];
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ action: "delete_user", name, email })
                            }).then(res => res.json()).then(data => {
                                outputLines.push(data.message);
                                renderTerminal();
                            });
                        } else if (args[1] === "product" && (role === "admin" || role === "owner")) {
                            const name = args[2];
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ action: "delete_product", name })
                            }).then(res => res.json()).then(data => {
                                outputLines.push(data.message);
                                renderTerminal();
                            });
                        } else {
                            outputLines.push("Invalid delete command.");
                        }
                        break;

                    case "list":
                        if (args[1] === "users" && (role === "admin" || role === "owner")) {
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ action: "list_users" })
                            }).then(res => res.json()).then(data => {
                                if (Array.isArray(data.users)) {
                                    outputLines.push("> All Users:");
                                    data.users.forEach(u => {
                                        let roleTag = u.user_type.toUpperCase();
                                        let color = roleTag === "ADMIN" ? "lime" : roleTag === "OWNER" ? "cyan" : "white";
                                        outputLines.push(`- ${u.name} (${u.email}) <span style="color:${color}; font-weight:bold;">[${roleTag}]</span>`);
                                    });
                                } else outputLines.push(data.message);
                                renderTerminal();
                            });
                        } else if (args[1] === "products") {
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ action: "list_products" })
                            }).then(res => res.json()).then(data => {
                                if (Array.isArray(data.products)) {
                                    outputLines.push("> All Products:");
                                    data.products.forEach(p => {
                                        outputLines.push(`- ${p.description} | £${p.price} | ${p.image}`);
                                    });
                                } else outputLines.push(data.message);
                                renderTerminal();
                            });
                        } else {
                            outputLines.push("Invalid list command.");
                        }
                        break;

                    case "clear":
                        outputLines = [];
                        terminal.innerHTML = "";
                        break;

                    case "logout":
                        window.location.href = "profile.php?logout=" + user_id;
                        return;

                    default:
                        const suggestion = suggestCommand(mainCmd);
                        outputLines.push(`Unrecognized command: ${cmd}`);
                        if (suggestion) outputLines.push(suggestion);
                }

                input = "";
                renderTerminal();
            } else if (e.key.length === 1) {
                input += e.key;
            }

            renderTerminal(input);
        });
    }

    function typeLines(lines, onComplete) {
        i = 0; j = 0; currentLine = "";
        function type() {
            if (i < lines.length) {
                if (j < lines[i].length) {
                    currentLine += lines[i][j++];
                    renderTerminal();
                    setTimeout(type, 20);
                } else {
                    outputLines.push(currentLine);
                    currentLine = "";
                    j = 0; i++;
                    renderTerminal();
                    setTimeout(type, 600);
                }
            } else {
                onComplete();
            }
        }
        type();
    }

    setTimeout(() => {
        audio?.play?.().catch(() => {
            document.body.addEventListener("click", () => audio.play(), { once: true });
        });
        typeLines(bootLines, () => {
            outputLines.length = 0;
            terminal.innerHTML = "";
            setTimeout(() => typeLines(welcomeLines, enableCommandInput), 400);
        });
    }, 1000);
});

function updateClock() {
    const now = new Date();
    const formatted = now.toLocaleDateString('en-GB') + ', ' + now.toLocaleTimeString('en-GB');
    document.getElementById("clock").textContent = formatted;
}
setInterval(updateClock, 1000);
updateClock();

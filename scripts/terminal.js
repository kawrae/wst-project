document.addEventListener("DOMContentLoaded", () => {
    const terminal = document.getElementById("terminal");
    const audio = document.getElementById("boot-sound");
    const cursor = "<span class='cursor'>■</span>";

    const bootLines = [
        // `> Initializing secure shell...`,
        // `> Booting WST project v1.0.0`,
        // `> Checking session integrity...`,
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
        terminal.scrollTop = terminal.scrollHeight;
    }

    function parseArguments(input) {
        const regex = /"([^"]+)"|\S+/g;
        const matches = [];
        let match;
        while ((match = regex.exec(input)) !== null) {
            matches.push(match[1] || match[0]);
        }
        return matches;
    }

    function enableCommandInput() {
        let input = "";
        renderTerminal(input);

        document.addEventListener("keydown", function handleKey(e) {
            if (e.key === "Backspace") {
                input = input.slice(0, -1);
            } else if (e.key === "Enter") {
                const cmd = input.trim();
                outputLines.push(`> ${cmd}`);
                const args = parseArguments(cmd);
                const mainCmd = args[0]?.toLowerCase();
                const role = userRole.toLowerCase();

                switch (mainCmd) {
                    case "help":
                        outputLines.push("> Available commands:");
                        outputLines.push('- page "profile/products"');
                        if (role === "admin" || role === "owner") {
                            outputLines.push('- add user "name" "email" <user_type>');
                            outputLines.push('- delete user "name" "email"');
                            outputLines.push('- add product "id" "description" "price" "image"');
                            outputLines.push('- edit product "id" "description" "price" "image"');
                            outputLines.push('- delete product "name"');
                            outputLines.push('- list users');
                        }
                        outputLines.push('- list products');
                        outputLines.push('- clear');
                        outputLines.push('- logout');
                        break;

                    case "page":
                        if (args[1]) {
                            const page = args[1].toLowerCase();
                            if (["profile", "products"].includes(page)) {
                                window.location.href = `${page}.php`;
                                return;
                            } else {
                                outputLines.push(`Page "${page}" not recognized.`);
                            }
                        } else {
                            outputLines.push('Usage: page "profile"');
                        }
                        break;

                    case "add":
                        if (args[1] === "user" && (role === "admin" || role === "owner")) {
                            const name = args[2];
                            const email = args[3];
                            const userTypeRaw = args.slice(4).join(" ");
                            const userTypeMatch = userTypeRaw.match(/<(.+?)>/);
                            const userType = userTypeMatch ? userTypeMatch[1].toLowerCase() : null;

                            if (!name || !email || !userType) {
                                outputLines.push('Usage: add user "name" "email" <user_type>');
                            } else if (userType === "owner" && role !== "owner") {
                                outputLines.push("Permission denied: only owners can create another owner.");
                            } else if (!["admin", "user", "owner"].includes(userType)) {
                                outputLines.push(`Invalid user type: <${userType}>`);
                            } else {
                                fetch("terminal_api.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        action: "add_user",
                                        name,
                                        email,
                                        user_type: userType
                                    })
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                        outputLines.push(data.message || "User added.");
                                        renderTerminal();
                                    });
                            }
                        } else if (args[1] === "product" && (role === "admin" || role === "owner")) {
                            const [, , id, description, price, image] = args;
                            if (id && description && price && image) {
                                fetch("terminal_api.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        action: "add_product",
                                        id,
                                        description,
                                        price,
                                        image
                                    })
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                        outputLines.push(data.message || "Product added.");
                                        renderTerminal();
                                    });
                            } else {
                                outputLines.push('Usage: add product "id" "description" "price" "image"');
                            }
                        } else {
                            outputLines.push("Invalid add command.");
                        }
                        break;

                    case "edit":
                        if (args[1] === "product" && (role === "admin" || role === "owner")) {
                            const [, , id, description, price, image] = args;
                            if (id && description && price && image) {
                                fetch("terminal_api.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        action: "edit_product",
                                        id,
                                        description,
                                        price,
                                        image
                                    })
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                        outputLines.push(data.message || "Product updated.");
                                        renderTerminal();
                                    });
                            } else {
                                outputLines.push('Usage: edit product "id" "description" "price" "image"');
                            }
                        } else {
                            outputLines.push("Invalid edit command.");
                        }
                        break;

                    case "delete":
                        if (args[1] === "user" && (role === "admin" || role === "owner")) {
                            const name = args[2];
                            const email = args[3];
                            if (name && email) {
                                fetch("terminal_api.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        action: "delete_user",
                                        name,
                                        email
                                    })
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                        outputLines.push(data.message || "User deleted.");
                                        renderTerminal();
                                    });
                            } else {
                                outputLines.push('Usage: delete user "name" "email"');
                            }
                        } else if (args[1] === "product" && (role === "admin" || role === "owner")) {
                            const name = args[2];
                            if (name) {
                                fetch("terminal_api.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({
                                        action: "delete_product",
                                        name
                                    })
                                })
                                    .then(res => res.json())
                                    .then(data => {
                                        outputLines.push(data.message || "Product deleted.");
                                        renderTerminal();
                                    });
                            } else {
                                outputLines.push('Usage: delete product "name"');
                            }
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
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (Array.isArray(data.users)) {
                                        outputLines.push("> All Users:");
                                        data.users.forEach(u => {
                                            outputLines.push(`- ${u.name} (${u.email}) [${u.user_type}]`);
                                        });
                                    } else {
                                        outputLines.push("Failed to fetch users.");
                                    }
                                    renderTerminal();
                                });
                        } else if (args[1] === "products") {
                            fetch("terminal_api.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ action: "list_products" })
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (Array.isArray(data.products)) {
                                        outputLines.push("> All Products:");
                                        data.products.forEach(p => {
                                            outputLines.push(`- ${p.description} | £${p.price} | ${p.image}`);
                                        });
                                    } else {
                                        outputLines.push("Failed to fetch products.");
                                    }
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
                        outputLines.push(`Unrecognized command: ${cmd}`);
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
        i = 0;
        j = 0;
        currentLine = "";

        function type() {
            if (i < lines.length) {
                if (j < lines[i].length) {
                    currentLine += lines[i][j++];
                    renderTerminal();
                    setTimeout(type, 20);
                } else {
                    outputLines.push(currentLine);
                    currentLine = "";
                    j = 0;
                    i++;
                    renderTerminal();
                    setTimeout(type, 800);
                }
            } else {
                onComplete();
            }
        }

        type();
    }

    const tryPlay = () => {
        audio.play().catch(() => {
            document.body.addEventListener("click", () => {
                audio.play();
            }, { once: true });
        });
    };

    setTimeout(() => {
        tryPlay();
        typeLines(bootLines, () => {
            outputLines = [];
            terminal.innerHTML = "";
            setTimeout(() => {
                typeLines(welcomeLines, enableCommandInput);
            }, 600);
        });
    }, 2000);
});

function updateClock() {
    const now = new Date();
    const formatted = now.toLocaleDateString('en-GB') + ', ' + now.toLocaleTimeString('en-GB');
    document.getElementById("clock").textContent = formatted;
}
setInterval(updateClock, 1000);
updateClock();

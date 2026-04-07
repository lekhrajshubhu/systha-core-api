<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Swagger UI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css" />
    <style>
        body,
        .swagger-ui,
        .swagger-ui * {
            font-family: "Poppins", sans-serif !important;
        }

        .swagger-ui .tag-group-wrapper {
            margin: 0 0 16px;
        }

        .swagger-ui .tag-group-title {
            font-size: 30px;
            font-weight: 700;
            margin: 24px 0 8px;
            color: #3b4151;
        }

        .swagger-ui .tag-group-subtitle {
            font-size: 14px;
            font-weight: 500;
            color: #6b7280;
            margin: -4px 0 12px;
        }

        .swagger-ui .tag-group-wrapper .opblock-tag-section {
            margin-left: 14px;
        }
		.opblock-summary-path{
			font-size: 14px !important;
		}

        a.nostyle {
            /* font-size: 1.2rem; */
        }
    </style>
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
    <script>
        const splitTag = (label) => {
            const parts = String(label || "").split("/");
            if (parts.length < 2) return null;
            const group = parts.shift().trim();
            const subgroup = parts.join("/").trim();
            if (!group || !subgroup) return null;
            return {
                group,
                subgroup
            };
        };

        const groupSubtitles = {
            Platform: "(Global API)",
            Tenant: "(Tenant specific API)",
        };

        const normalizeTagSections = () => {
            const sections = Array.from(document.querySelectorAll(".swagger-ui .opblock-tag-section"));
            if (!sections.length) return;
            if (sections.some((section) => section.closest(".tag-group-wrapper"))) return;

            const groups = new Map();
            const plainSections = [];

            sections.forEach((section) => {
                const labelNode = section.querySelector(".opblock-tag .nostyle span");
                const parsed = splitTag(labelNode ? labelNode.textContent : "");
                if (!parsed) {
                    plainSections.push(section);
                    return;
                }

                labelNode.textContent = parsed.subgroup;
                if (!groups.has(parsed.group)) groups.set(parsed.group, []);
                groups.get(parsed.group).push(section);
            });

            const container = sections[0].parentElement;
            if (!container) return;

            container.innerHTML = "";

<<<<<<< HEAD
	            groups.forEach((groupSections, groupName) => {
	                groupSections.sort((a, b) => {
	                    const aLabel = a.querySelector(".opblock-tag .nostyle span")?.textContent?.trim() || "";
	                    const bLabel = b.querySelector(".opblock-tag .nostyle span")?.textContent?.trim() || "";
	                    return aLabel.localeCompare(bLabel, undefined, {
	                        sensitivity: "base"
	                    });
	                });

	                const wrapper = document.createElement("section");
	                wrapper.className = "tag-group-wrapper";
=======
            groups.forEach((groupSections, groupName) => {
                const wrapper = document.createElement("section");
                wrapper.className = "tag-group-wrapper";
>>>>>>> 18539635f4a2a7c24ea1f527231dffef47b3d97a

                const title = document.createElement("h2");
                title.className = "tag-group-title";
                title.textContent = groupName;

                wrapper.appendChild(title);
                if (groupSubtitles[groupName]) {
                    const subtitle = document.createElement("div");
                    subtitle.className = "tag-group-subtitle";
                    subtitle.textContent = groupSubtitles[groupName];
                    wrapper.appendChild(subtitle);
                }
                groupSections.forEach((section) => wrapper.appendChild(section));
                container.appendChild(wrapper);
            });

            plainSections.forEach((section) => container.appendChild(section));
        };

        window.ui = SwaggerUIBundle({
            url: "/docs.openapi",
            dom_id: "#swagger-ui",
            deepLinking: true,
            displayOperationId: true,
            onComplete: function() {
                normalizeTagSections();
                const root = document.querySelector("#swagger-ui");
                if (!root) return;
                const observer = new MutationObserver(() => normalizeTagSections());
                observer.observe(root, {
                    childList: true,
                    subtree: true
                });
            }
        });
    </script>
</body>

</html>

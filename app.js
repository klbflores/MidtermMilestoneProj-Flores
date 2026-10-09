document.addEventListener("DOMContentLoaded", () => {
    // 1. Dynamic Ingredient Rows in recipe_create & recipe_edit
    const addIngBtn = document.getElementById("btn-add-ingredient");
    const ingContainer = document.getElementById("ingredient-rows");

    if (addIngBtn && ingContainer) {
        addIngBtn.addEventListener("click", () => {
            const row = document.createElement("div");
            row.className = "ingredient-row";
            row.innerHTML = `
                <input type="text" name="ingredient_name[]" placeholder="Item name (e.g., Toyo, Garlic)" required>
                <input type="text" name="ingredient_qty[]" placeholder="Quantity (e.g., 2 tbsp)" required>
                <button type="button" class="btn btn-danger btn-remove-row">✕</button>
            `;
            ingContainer.appendChild(row);
            updateRemoveButtons();
        });

        ingContainer.addEventListener("click", (e) => {
            if (e.target.classList.contains("btn-remove-row")) {
                const rows = ingContainer.querySelectorAll(".ingredient-row");
                if (rows.length > 1) {
                    e.target.closest(".ingredient-row").remove();
                    updateRemoveButtons();
                }
            }
        });

        function updateRemoveButtons() {
            const rows = ingContainer.querySelectorAll(".ingredient-row");
            rows.forEach((r) => {
                const btn = r.querySelector(".btn-remove-row");
                if (btn) btn.style.display = rows.length > 1 ? "inline-block" : "none";
            });
        }
        updateRemoveButtons();
    }

    // 2. Inline Comment Editing Toggle
    document.querySelectorAll(".btn-edit-comment").forEach((btn) => {
        btn.addEventListener("click", () => {
            const commentId = btn.dataset.id;
            const textEl = document.getElementById(`comment-text-${commentId}`);
            const formEl = document.getElementById(`edit-form-${commentId}`);
            if (textEl && formEl) {
                textEl.style.display = "none";
                formEl.style.display = "block";
            }
        });
    });

    document.querySelectorAll(".btn-cancel-edit").forEach((btn) => {
        btn.addEventListener("click", () => {
            const commentId = btn.dataset.id;
            const textEl = document.getElementById(`comment-text-${commentId}`);
            const formEl = document.getElementById(`edit-form-${commentId}`);
            if (textEl && formEl) {
                textEl.style.display = "block";
                formEl.style.display = "none";
            }
        });
    });

    // 3. Asynchronous Favorites Toggle (No Page Reload)
    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".fav-btn");
        if (!btn) return;

        const recipeId = btn.dataset.id;
        try {
            const response = await fetch("toggle_favorite.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ recipe_id: recipeId })
            });

            if (!response.ok) throw new Error("Network error toggling favorite");

            const data = await response.json();
            if (data.success) {
                if (data.favorited) {
                    btn.classList.add("active");
                    btn.textContent = btn.classList.contains("btn-large") ? "❤️ Saved" : "❤️";
                } else {
                    btn.classList.remove("active");
                    btn.textContent = btn.classList.contains("btn-large") ? "🤍 Save to Favorites" : "🤍";
                }
            }
        } catch (err) {
            console.error("Favorite toggle failed:", err);
            alert("Could not update bookmark. Please try again.");
        }
    });
});
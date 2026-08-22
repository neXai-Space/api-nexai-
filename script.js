// Fitur Copy Manual sesuai request
function copyApiKey() {
    const apiKeyInput = document.getElementById("apiKeyInput");
    
    // Select the text field
    apiKeyInput.select();
    apiKeyInput.setSelectionRange(0, 99999); // Untuk support mobile

    // Copy the text inside the text field
    navigator.clipboard.writeText(apiKeyInput.value).then(() => {
        // Beri feedback visual ke user
        const btn = document.querySelector('.btn-copy');
        const originalText = btn.innerText;
        btn.innerText = "Tercopy!";
        btn.style.background = "#4caf50";
        btn.style.color = "white";
        
        setTimeout(() => {
            btn.innerText = originalText;
            btn.style.background = "#2a2a35";
            btn.style.color = "white";
        }, 2000);
    }).catch(err => {
        console.error('Gagal mengcopy text: ', err);
    });
          }

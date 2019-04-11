(() => {
    const themeChanger = document.querySelector(`.theme-changer`);
    const shorten = document.querySelector(`.shorten`);
    const input = document.querySelector(`.url`);
    const btn = document.querySelector(`.btn`);
    const result = document.querySelector(`.result`);
    const shortUrl = document.querySelector(`.short-url`);

    if (localStorage.light === `true`) {
        document.body.classList.add(`light`);
    }

    themeChanger.addEventListener(`click`, () => {
        document.body.classList.toggle(`light`);
        localStorage.light = document.body.classList.contains(`light`);
    });

    btn.addEventListener(`click`, () => {
        const url = input.value;

        if (url.match(/^(https?:\/\/)?\S*?\.\S*?$/)) {
            fetch(`api.php`, {
                method: `POST`,
                body: JSON.stringify({ url }),
            })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        shorten.style.display = `none`;
                        result.style.display = `block`;
                        shortUrl.innerText = shortUrl.href = res.short_url;
                    }
                });
        } else {
            alert(`Invalid url.`);
        }
    });
})();

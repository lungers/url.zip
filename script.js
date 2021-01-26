(() => {
    const themeChanger = document.querySelector(`.theme-changer`);
    const shorten = document.querySelector(`.shorten`);
    const shortenUrl = document.querySelector(`.shorten__url`);
    const shortenButton = document.querySelector(`.shorten__button`);
    const result = document.querySelector(`.result`);
    const resultUrl = document.querySelector(`.result__url`);

    if (localStorage.light === `true`) {
        document.body.classList.add(`light`);
    }

    themeChanger.addEventListener(`click`, () => {
        document.body.classList.toggle(`light`);
        localStorage.light = document.body.classList.contains(`light`);
    });

    shortenButton.addEventListener(`click`, () => {
        const url = shortenUrl.value;

        if (url.match(/^(https?:\/\/)?\S*?\.\S*?$/)) {
            fetch(`https://s.lungers.com/api.php`, {
                    method: `POST`,
                    body: JSON.stringify({ url }),
                })
                .then(res => res.json())
                .then(res => {
                    if (res.ok) {
                        shorten.classList.add(`shorten--hidden`);
                        result.classList.add(`result--visible`);

                        resultUrl.innerText = resultUrl.href = res.short_url;
                    }
                });
        } else {
            alert(`Invalid url.`);
        }
    });
})();

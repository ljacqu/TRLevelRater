function handleRating(inputElem) {
    const inputValue = inputElem.value.trim();
    let rating;
    if (/^[1-5]\.?$/.test(inputValue)) {
        const decimalSuffix = inputValue.slice(-1) === '.' ? '0' : '.0';
        rating = inputValue + decimalSuffix;
    } else if (/^([1-4]\.\d)|(5\.0)$/.test(inputValue)) {
        rating = inputValue;
    } else {
        inputElem.className = 'ratingerror';
        inputElem.title = 'Please use a rating from 1.0 to 5.0';
        return;
    }

    inputElem.disabled = true;
    inputElem.title = '';

    const formData = new FormData();
    formData.append('rating', rating);
    formData.append('level', inputElem.id);
    const request = new Request('websaverating.php', {
        method: 'POST',
        body: formData
    });

    fetch(request)
        .then(response => response.text())
        .then(result=> {
            if (result === 'Success') {
                deleteElementIfExists(inputElem.parentElement, 'p');
                inputElem.value = rating;
                inputElem.className = 'ratingsaved';
            } else {
                getOrCreateElement(inputElem.parentElement, 'p').innerText = result;
                inputElem.className = 'ratingerror';
            }
        })
        .catch(e => {
            inputElem.className = 'ratingerror';
            getOrCreateElement(inputElem.parentElement, 'p').innerText = e.message;
        })
        .finally(() => {
            inputElem.disabled = false;
        });
}

function getOrCreateElement(parentElem, tagName) {
    const tag = parentElem.querySelector(tagName);
    if (tag) {
        return tag;
    }

    const newTag = document.createElement(tagName);
    parentElem.appendChild(newTag);
    return newTag;
}

function deleteElementIfExists(parentElem, tagName) {
    const elem = parentElem.querySelector(tagName);
    if (elem) {
        elem.remove();
    }
}

function initRatingCells() {
    for (const inputElem of document.querySelectorAll('.editablerating input')) {
        inputElem.onchange = () => handleRating(inputElem);
    }
}

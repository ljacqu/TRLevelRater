function handleRating(inputElem) {
    const inputValue = inputElem.value;
    let rating;
    if (/^[1-5]$/.test(inputValue)) {
        rating = inputValue + '.0';
    } else if (/^([1-4]\.\d)|(5\.0)$/.test(inputValue)) {
        rating = inputValue;
    } else {
        inputElem.className = 'ratingerror';
        return;
    }

    inputElem.disabled = true;

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

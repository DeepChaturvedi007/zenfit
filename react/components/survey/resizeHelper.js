let height = 0;

const watch = (element, cb) => {
    setInterval(() => {
        if(height !== element.scrollHeight) {
            height = element.scrollHeight;
            cb(height);
        }
    }, 100);
};

if (window.self !== window.top) {
    watch(document.body, (height) => {
        window.parent.postMessage(JSON.stringify({
            message: 'Height change',
            height
        }), "*");
    });
}

// Example of usage inside an external iFrame
//
// <iframe id="custom" src="/{domain}/lead/survey/${userId"></iframe>
// <script>
//     const enableListener = (selector) => {
//         const iFrame = document.querySelector(selector);
//         const frameSrc = (iFrame || {}).src;
//         if(!frameSrc) return;
//
//         window.addEventListener("message", function (event) {
//             if (!frameSrc.includes(event.origin)) return false;
//             let height;
//             try {
//                 const data = JSON.parse(event.data);
//                 height = `${data.height}px`;
//             } catch (e) {
//                 height = '90vh';
//             }
//             iFrame.height = height;
//         }, false);
//     };
//     enableListener('#custom');
// </script>


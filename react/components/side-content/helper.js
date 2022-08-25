export function prepareMessageUsingTags(message, placeholders, toActualValues) {

    if (message == null) {
        return '';
    }

    Object.keys(placeholders).forEach(placeholder => {
        for (var i = 0; i < 2; i++) {
            message = toActualValues ? message.replace(placeholders[placeholder], `[${placeholder}]`) : message.replace(`[${placeholder}]`, placeholders[placeholder])
        }
    });
    return message;
}

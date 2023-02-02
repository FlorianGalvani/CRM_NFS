export class Formatter {
    static formatDate = (
        dateTimeString,
        options = {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric'
        },
        locale = 'fr-FR',
    ) => {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString(locale, options);
    }
}
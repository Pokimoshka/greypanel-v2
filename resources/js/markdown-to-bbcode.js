export function markdownToBbcode(md) {
    let bb = md;
    // Жирный
    bb = bb.replace(/\*\*(.*?)\*\*/g, '[b]$1[/b]');
    bb = bb.replace(/__(.*?)__/g, '[b]$1[/b]');
    // Курсив
    bb = bb.replace(/\*(.*?)\*/g, '[i]$1[/i]');
    bb = bb.replace(/_(.*?)_/g, '[i]$1[/i]');
    // Заголовки (превращаем в жирный)
    bb = bb.replace(/^### (.*?)$/gm, '[b]$1[/b]');
    bb = bb.replace(/^## (.*?)$/gm, '[b]$1[/b]');
    bb = bb.replace(/^# (.*?)$/gm, '[b]$1[/b]');
    // Ссылки [text](url)
    bb = bb.replace(/\[(.*?)\]\((.*?)\)/g, '[url=$2]$1[/url]');
    // Изображения ![alt](url)
    bb = bb.replace(/!\[(.*?)\]\((.*?)\)/g, '[img]$2[/img]');
    // Цитаты
    bb = bb.replace(/^> (.*?)$/gm, '[quote]$1[/quote]');
    // Код
    bb = bb.replace(/`(.*?)`/g, '[code]$1[/code]');
    // Списки не трогаем
    return bb;
}
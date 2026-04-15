export function markdownToBbcode(md) {
    let bb = md;
    bb = bb.replace(/\*\*(.*?)\*\*/g, '[b]$1[/b]');
    bb = bb.replace(/__(.*?)__/g, '[b]$1[/b]');
    bb = bb.replace(/\*(.*?)\*/g, '[i]$1[/i]');
    bb = bb.replace(/_(.*?)_/g, '[i]$1[/i]');
    bb = bb.replace(/^### (.*?)$/gm, '[b]$1[/b]');
    bb = bb.replace(/^## (.*?)$/gm, '[b]$1[/b]');
    bb = bb.replace(/^# (.*?)$/gm, '[b]$1[/b]');
    bb = bb.replace(/\[(.*?)\]\((.*?)\)/g, '[url=$2]$1[/url]');
    bb = bb.replace(/!\[(.*?)\]\((.*?)\)/g, '[img]$2[/img]');
    bb = bb.replace(/^> (.*?)$/gm, '[quote]$1[/quote]');
    bb = bb.replace(/`(.*?)`/g, '[code]$1[/code]');
    return bb;
}
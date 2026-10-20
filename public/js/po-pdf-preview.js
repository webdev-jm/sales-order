/**
 * PO PDF preview for the scan review screens.
 *
 * Draws every page of the PDF with PDF.js (public/vendor/pdfjs) and keeps the text printed on each page, so a
 * value can be found again: a "pdf-find" window event with { terms: [...], page, row } marks where any of the
 * terms is printed, the whole line when row is true. A scanned PDF that is only an image has no text to search;
 * the preview then scrolls to the page the AI named and says the value cannot be marked.
 *
 * Use: <div x-data="poPdfPreview(pdfUrl, libUrl, workerUrl)" x-on:pdf-find.window="find($event.detail)">
 *        <div x-ref="pages"></div></div>
 */
window.poPdfPreview = function (pdfUrl, libUrl, workerUrl) {
    var normalize = function (text) {
        return String(text || '').toLowerCase().replace(/[\s,]+/g, '').replace(/[^a-z0-9.\/-]/g, '');
    };

    return {
        status: 'Loading the PDF…',
        message: '',
        pageCount: 0,
        pages: [],
        lib: null,

        init: function () {
            var self = this;
            import(libUrl).then(function (pdfjs) {
                self.lib = pdfjs;
                pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;
                return pdfjs.getDocument({ url: pdfUrl, isEvalSupported: false, withCredentials: true }).promise;
            }).then(function (doc) {
                self.pageCount = doc.numPages;
                var chain = Promise.resolve();
                for (var n = 1; n <= doc.numPages; n++) {
                    chain = chain.then(self.renderPage.bind(self, doc, n));
                }
                return chain;
            }).then(function () {
                self.status = '';
            }).catch(function () {
                self.status = 'The PDF could not be shown here. Open it from the link above instead.';
            });
        },

        renderPage: function (doc, number) {
            var self = this;
            return doc.getPage(number).then(function (page) {
                var holder = self.$refs.pages;
                var width = Math.max(320, holder.clientWidth - 2);
                var viewport = page.getViewport({ scale: width / page.getViewport({ scale: 1 }).width });
                var ratio = window.devicePixelRatio || 1;

                var wrap = document.createElement('div');
                wrap.className = 'po-page';
                wrap.style.width = viewport.width + 'px';
                wrap.style.height = viewport.height + 'px';
                wrap.setAttribute('aria-label', 'Page ' + number);

                var canvas = document.createElement('canvas');
                canvas.width = Math.floor(viewport.width * ratio);
                canvas.height = Math.floor(viewport.height * ratio);
                canvas.style.width = viewport.width + 'px';
                canvas.style.height = viewport.height + 'px';
                wrap.appendChild(canvas);

                var label = document.createElement('span');
                label.className = 'po-page-number';
                label.textContent = number + ' / ' + doc.numPages;
                wrap.appendChild(label);
                holder.appendChild(wrap);

                return page.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport,
                    transform: ratio !== 1 ? [ratio, 0, 0, ratio, 0, 0] : null
                }).promise.then(function () {
                    return page.getTextContent();
                }).then(function (text) {
                    self.pages.push({
                        number: number,
                        wrap: wrap,
                        viewport: viewport,
                        items: text.items.filter(function (item) { return item.str && item.str.trim() !== ''; }).map(function (item) {
                            return { norm: normalize(item.str), transform: item.transform, width: item.width };
                        })
                    });
                    self.pages.sort(function (a, b) { return a.number - b.number; });
                });
            });
        },

        clear: function () {
            this.$refs.pages.querySelectorAll('.po-mark').forEach(function (mark) { mark.remove(); });
            this.message = '';
        },

        find: function (detail) {
            var self = this;
            if (!this.lib || !this.pages.length) {
                return;
            }
            this.clear();

            var terms = (detail.terms || []).map(normalize).filter(function (term) { return term.length >= 2; });
            var ordered = this.pages.slice().sort(function (a, b) {
                return (a.number === detail.page ? -1 : 0) - (b.number === detail.page ? -1 : 0);
            });

            var found = null;
            ordered.some(function (page) {
                return page.items.some(function (item) {
                    var hit = terms.some(function (term) {
                        return item.norm === term || (term.length >= 4 && item.norm.indexOf(term) !== -1) || (item.norm.length >= 4 && term.indexOf(item.norm) !== -1);
                    });
                    if (hit) {
                        found = { page: page, item: item };
                    }
                    return hit;
                });
            });

            if (!found) {
                var target = this.pages.find(function (page) { return page.number === detail.page; }) || this.pages[0];
                target.wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                this.message = terms.length
                    ? 'This value is not printed as text on the PDF (it may be a scanned image), so it cannot be marked.' + (detail.page ? ' The AI read it on page ' + detail.page + '.' : '')
                    : '';
                return;
            }

            var tx = this.lib.Util.transform(found.page.viewport.transform, found.item.transform);
            var height = Math.hypot(tx[2], tx[3]);
            var mark = document.createElement('div');
            mark.className = 'po-mark' + (detail.row ? ' po-mark-row' : '');
            mark.style.top = (tx[5] - height * 1.05) + 'px';
            mark.style.height = (height * 1.35) + 'px';
            if (detail.row) {
                mark.style.left = '2%';
                mark.style.width = '96%';
            } else {
                mark.style.left = (tx[4] - 3) + 'px';
                mark.style.width = (found.item.width * found.page.viewport.scale + 6) + 'px';
            }
            found.page.wrap.appendChild(mark);
            mark.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };
};

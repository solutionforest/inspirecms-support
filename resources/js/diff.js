document.addEventListener('alpine:init', () => {
    window.Alpine.data('diffChecker', function ({
        newValue,
        oldValue,
    }) {
        return {
            oldValue,
            newValue,
            diff: [],

            init() {
                this.calculateDiff();
            },

            getOldValue() {
                return this.oldValue || '';
            },

            getNewValue() {
                return this.newValue || '';
            },
                
            calculateDiff() {
                const oldValue = this.getOldValue();
                const newValue = this.getNewValue();

                if (!oldValue && !newValue) {
                    this.diff = [];
                    return;
                }

                // Split into words while preserving whitespace
                const oldWords = this.tokenize(oldValue);
                const newWords = this.tokenize(newValue);
                
                this.diff = this.generateWordDiff(oldWords, newWords);
            },

            tokenize(text) {
                // Split text into words, whitespace, and punctuation
                const tokens = [];
                const regex = /(\S+|\s+)/g;
                let match;
                
                while ((match = regex.exec(text)) !== null) {
                    tokens.push(match[1]);
                }
                
                return tokens;
            },

            generateWordDiff(oldWords, newWords) {
                const diff = [];
                let oldIndex = 0;
                let newIndex = 0;
                
                // Use LCS algorithm for word-level diff
                const lcs = this.longestCommonSubsequence(oldWords, newWords);
                let lcsIndex = 0;
                
                while (oldIndex < oldWords.length || newIndex < newWords.length) {
                    const oldWord = oldWords[oldIndex];
                    const newWord = newWords[newIndex];
                    
                    if (oldIndex < oldWords.length && newIndex < newWords.length && oldWord === newWord) {
                        // Words are identical
                        diff.push({
                            type: 'unchanged',
                            content: oldWord
                        });
                        oldIndex++;
                        newIndex++;
                    } else {
                        // Look ahead to find next matching sequence
                        let foundMatch = false;
                        const maxLookAhead = 20; // Limit look-ahead to prevent performance issues
                        
                        // Try to find the next common word
                        for (let oldSkip = 0; oldSkip <= maxLookAhead && oldIndex + oldSkip < oldWords.length; oldSkip++) {
                            for (let newSkip = 0; newSkip <= maxLookAhead && newIndex + newSkip < newWords.length; newSkip++) {
                                if (oldWords[oldIndex + oldSkip] === newWords[newIndex + newSkip] &&
                                    lcs.includes(oldWords[oldIndex + oldSkip])) {
                                    
                                    // Add removed words
                                    for (let i = 0; i < oldSkip; i++) {
                                        diff.push({
                                            type: 'removed',
                                            content: oldWords[oldIndex + i]
                                        });
                                    }
                                    
                                    // Add added words
                                    for (let i = 0; i < newSkip; i++) {
                                        diff.push({
                                            type: 'added',
                                            content: newWords[newIndex + i]
                                        });
                                    }
                                    
                                    oldIndex += oldSkip;
                                    newIndex += newSkip;
                                    foundMatch = true;
                                    break;
                                }
                            }
                            if (foundMatch) break;
                        }
                        
                        if (!foundMatch) {
                            // No common word found, process remaining words
                            if (oldIndex < oldWords.length && newIndex < newWords.length) {
                                // Both have words, mark as changed
                                diff.push({
                                    type: 'removed',
                                    content: oldWord
                                });
                                diff.push({
                                    type: 'added',
                                    content: newWord
                                });
                                oldIndex++;
                                newIndex++;
                            } else if (oldIndex < oldWords.length) {
                                // Only old has words left, mark as removed
                                diff.push({
                                    type: 'removed',
                                    content: oldWord
                                });
                                oldIndex++;
                            } else if (newIndex < newWords.length) {
                                // Only new has words left, mark as added
                                diff.push({
                                    type: 'added',
                                    content: newWord
                                });
                                newIndex++;
                            }
                        }
                    }
                }
                
                return diff;
            },

            generateDiff(oldLines, newLines) {
                const diff = [];
                let oldIndex = 0;
                let newIndex = 0;
                
                // Simple diff algorithm using longest common subsequence approach
                const lcs = this.longestCommonSubsequence(oldLines, newLines);
                
                while (oldIndex < oldLines.length || newIndex < newLines.length) {
                    const oldLine = oldLines[oldIndex];
                    const newLine = newLines[newIndex];
                    
                    // Check if current lines are in LCS
                    const oldInLCS = lcs.includes(oldLine) && oldIndex < oldLines.length;
                    const newInLCS = lcs.includes(newLine) && newIndex < newLines.length;
                    
                    if (oldIndex < oldLines.length && newIndex < newLines.length && oldLine === newLine) {
                        // Lines are identical
                        diff.push({
                            type: 'unchanged',
                            content: oldLine,
                            oldLineNum: oldIndex + 1,
                            newLineNum: newIndex + 1
                        });
                        oldIndex++;
                        newIndex++;
                    } else if (oldIndex < oldLines.length && (!newInLCS || newIndex >= newLines.length || !oldInLCS)) {
                        // Line was removed
                        diff.push({
                            type: 'removed',
                            content: oldLine,
                            oldLineNum: oldIndex + 1,
                            newLineNum: null
                        });
                        oldIndex++;
                    } else if (newIndex < newLines.length) {
                        // Line was added
                        diff.push({
                            type: 'added',
                            content: newLine,
                            oldLineNum: null,
                            newLineNum: newIndex + 1
                        });
                        newIndex++;
                    }
                }
                
                return diff;
            },

            longestCommonSubsequence(arr1, arr2) {
                const m = arr1.length;
                const n = arr2.length;
                const dp = Array(m + 1).fill().map(() => Array(n + 1).fill(0));
                
                for (let i = 1; i <= m; i++) {
                    for (let j = 1; j <= n; j++) {
                        if (arr1[i - 1] === arr2[j - 1]) {
                            dp[i][j] = dp[i - 1][j - 1] + 1;
                        } else {
                            dp[i][j] = Math.max(dp[i - 1][j], dp[i][j - 1]);
                        }
                    }
                }
                
                // Reconstruct LCS
                const lcs = [];
                let i = m, j = n;
                while (i > 0 && j > 0) {
                    if (arr1[i - 1] === arr2[j - 1]) {
                        lcs.unshift(arr1[i - 1]);
                        i--;
                        j--;
                    } else if (dp[i - 1][j] > dp[i][j - 1]) {
                        i--;
                    } else {
                        j--;
                    }
                }
                
                return lcs;
            },
            
            getInlineDiff() {
                if (this.diff.length === 0) return '';
                
                let inlineHtml = '';
                
                for (let i = 0; i < this.diff.length; i++) {
                    const word = this.diff[i];
                    
                    if (word.type === 'unchanged') {
                        inlineHtml += this.escapeHtml(word.content);
                    } else if (word.type === 'removed') {
                        inlineHtml += `<span class="deleted-text">${this.escapeHtml(word.content)}</span>`;
                    } else if (word.type === 'added') {
                        inlineHtml += `<span class="added-text">${this.escapeHtml(word.content)}</span>`;
                    }
                }
                
                return inlineHtml;
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },
        }
    });
});
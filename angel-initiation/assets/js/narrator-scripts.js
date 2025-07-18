/**
 * Angel Initiation Narrator Scripts - Updated with Better Timing
 * All dialogue and animation sequences with slower, more readable timing
 */

class NarratorScripts {
    static getScript(phase) {
        const scripts = {
            // Boot sequence - narrator wakes up
            boot: [
                {
                    frames: ["😴", "💤", "😴"],
                    speed: 800,
                    duration: 5000
                },
                {
                    frames: ["😴", "😪", "😌", "😊"],
                    speed: 1000,
                    text: "Oh—hi! You're awake. Took your time, babe...",
                    duration: 5000
                }
            ],
            
            // Greeting phase - much slower timing
            greeting: [
                {
                    frames: ["😄", "😁", "😊"],
                    speed: 700,
                    text: "Welcome to Green Angel — the sacred space for vibe-certified legends only.",
                    duration: 7000
                },
                {
                    frames: ["😄", "😁", "😊"],
                    speed: 700,
                    text: "I'm your guide. Here to make your experience even more magical.",
                    duration: 6000
                },
                {
                    frames: ["😄", "😁", "😊"],
                    speed: 700,
                    text: "Before you float off into the cosmos, I just need a few tiny things from you...",
                    duration: 7000
                },
                {
                    frames: ["😄", "😁", "😊"],
                    speed: 700,
                    text: "This realm is *invitation only* — for the dopest angels who pass the VIBE CHECK™.",
                    duration: 8000
                },
                {
                    frames: ["😄", "😁", "😊"],
                    speed: 700,
                    text: "We're here to bring FUN back to the internet. Don't you think?",
                    duration: 6000
                },
                {
                    frames: ["😄", "😁", "😊"],
                    speed: 700,
                    text: "So... if you're ready, shall we begin?",
                    duration: 5000
                }
            ],
            
            // Angel Identity phase - longer timing
            angelIdentity: [
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "Ooooh, exciting part — it's time to choose your Angel Identity.",
                    duration: 6000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "Think of this as your *reverb*... your cosmic codename.",
                    duration: 6000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "Of course it's cannabis-coded, babe. What else would it be?",
                    duration: 6000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "But here's the twist...",
                    duration: 3000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "You only get **FIVE** chances. That's it.",
                    duration: 4000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "And once you skip a name... it's gone. Like a puff of smoke.",
                    duration: 6000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "If you feel it — that *YESSS* — lock it in.",
                    duration: 5000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "Or roll the dice, and tempt fate.",
                    duration: 4000
                },
                {
                    frames: ["😏", "😳", "😄"],
                    speed: 600,
                    text: "When you're ready... tap ROLL.",
                    duration: 4000
                }
            ],
            
            // Name result reaction
            nameResult: [
                {
                    frames: ["🤯", "😄", "😏"],
                    speed: 500,
                    text: "That's pretty fucking sick, if I do say so myself.",
                    duration: 5000
                },
                {
                    frames: ["🤯", "😄", "😏"],
                    speed: 500,
                    text: "Looking at you... yeah. That name was *always* yours.",
                    duration: 5000
                },
                {
                    frames: ["🤯", "😄", "😏"],
                    speed: 500,
                    text: "Iconic. Let's lock it in.",
                    duration: 4000
                }
            ],
            
            // Tribe sorting intro
            tribeSorting: [
                {
                    frames: ["😲", "🤯", "😱"],
                    speed: 600,
                    text: "Now it's time to choose your tribe — your celestial crew.",
                    duration: 6000
                },
                {
                    frames: ["😲", "🤯", "😱"],
                    speed: 600,
                    text: "These are called Angel Tribes — each one's a vibe, a frequency, a lifestyle.",
                    duration: 7000
                },
                {
                    frames: ["😲", "🤯", "😱"],
                    speed: 600,
                    text: "It's random... kind of. Fate's got a plan. You just pick a bag.",
                    duration: 6000
                },
                {
                    frames: ["😲", "🤯", "😱"],
                    speed: 600,
                    text: "This feature's coming soon — but we're planning competitions, leaderboards, races...",
                    duration: 8000
                },
                {
                    frames: ["😲", "🤯", "😱"],
                    speed: 600,
                    text: "...and prizes for you *and* your tribe.",
                    duration: 5000
                },
                {
                    frames: ["😲", "🤯", "😱"],
                    speed: 600,
                    text: "Now go ahead... shuffle the bag and trust the universe.",
                    duration: 6000
                }
            ],
            
            // Tribe result reaction
            tribeResult: [
                {
                    frames: ["🫢", "🤩", "😮"],
                    speed: 500,
                    text: "Ohhh snap — you're in...",
                    duration: 3000
                },
                {
                    frames: ["🫢", "🤩", "😮"],
                    speed: 500,
                    text: "✨The Holy Smokes✨",
                    duration: 4000
                },
                {
                    frames: ["🫢", "🤩", "😮"],
                    speed: 500,
                    text: "Legend says their leader once hotboxed a dragon's cave and lived to tell the tale.",
                    duration: 8000
                },
                {
                    frames: ["🫢", "🤩", "😮"],
                    speed: 500,
                    text: "You were *born* for this tribe, babe.",
                    duration: 4000
                },
                {
                    frames: ["🫢", "🤩", "😮"],
                    speed: 500,
                    text: "Let's make them proud.",
                    duration: 3000
                }
            ],
            
            // Cosmic alignment (DOB)
            cosmicAlignment: [
                {
                    frames: ["😌", "😎", "🙂"],
                    speed: 700,
                    text: "Last bit, promise.",
                    duration: 3000
                },
                {
                    frames: ["😌", "😎", "🙂"],
                    speed: 700,
                    text: "I just need your birth month and year — that's it.",
                    duration: 5000
                },
                {
                    frames: ["😌", "😎", "🙂"],
                    speed: 700,
                    text: "Not your full birthday — just your cosmic arrival window.",
                    duration: 6000
                },
                {
                    frames: ["😌", "😎", "🙂"],
                    speed: 700,
                    text: "It's just so we can keep this space 18+ and sacred.",
                    duration: 6000
                },
                {
                    frames: ["😌", "😎", "🙂"],
                    speed: 700,
                    text: "So if you're lying... well, that's on you, babe. 😉",
                    duration: 5000
                }
            ],
            
            // Final blessing
            finalBlessing: [
                {
                    frames: ["🥹", "😇", "😌"],
                    speed: 700,
                    text: "Cosmic alignment complete.",
                    duration: 4000
                },
                {
                    frames: ["🥹", "😇", "😌"],
                    speed: 700,
                    text: "You're now officially initiated.",
                    duration: 4000
                },
                {
                    frames: ["🥹", "😇", "😌"],
                    speed: 700,
                    text: "Welcome home, Green Angel.",
                    duration: 4000
                },
                {
                    frames: ["🥹", "😇", "😌"],
                    speed: 700,
                    text: "We've been waiting for you.",
                    duration: 4000
                }
            ],
            
            // Quick reactions for various moments
            rolling: [
                {
                    frames: ["😄", "🤩", "😮"],
                    speed: 400,
                    text: "Ooh, the dice are rolling!",
                    duration: 3000
                }
            ],
            
            shuffling: [
                {
                    frames: ["😵‍💫", "🤯", "😱"],
                    speed: 500,
                    text: "The cosmic forces are at work!",
                    duration: 4000
                }
            ],
            
            thinking: [
                {
                    frames: ["🤔", "🧠", "🤔"],
                    speed: 800,
                    text: "Hmm...",
                    duration: 2000
                }
            ],
            
            celebration: [
                {
                    frames: ["😄", "🤩", "😁"],
                    speed: 400,
                    text: "YES! Absolutely perfect!",
                    duration: 4000
                }
            ]
        };
        
        return scripts[phase] || [];
    }
    
    // Dynamic tribe result with actual tribe name
    static getTribeResultScript(tribeName, tribeEmoji) {
        return [
            {
                frames: ["🤩", "😮", "🔥"],
                speed: 500,
                text: "Ohhh snap — you're in...",
                duration: 3000
            },
            {
                frames: ["🤩", "😮", "🔥"],
                speed: 500,
                text: `✨${tribeName}✨`,
                duration: 4000
            },
            {
                frames: ["🤩", "😮", "🔥"],
                speed: 500,
                text: this.getTribeLegend(tribeName),
                duration: 8000
            },
            {
                frames: ["🤩", "😮", "🔥"],
                speed: 500,
                text: "You were *born* for this tribe, babe.",
                duration: 4000
            },
            {
                frames: ["🤩", "😮", "🔥"],
                speed: 500,
                text: "Let's make them proud.",
                duration: 3000
            }
        ];
    }
    
    static getTribeLegend(tribeName) {
        const legends = {
            "The Dank Dynasty": "Legend says they once turned a royal palace into the world's largest hotbox.",
            "The Blazed Ones": "Ancient tales speak of their eternal chill and legendary munchie quests.",
            "The Holy Smokes": "Legend says their leader once hotboxed a dragon's cave and lived to tell the tale."
        };
        
        return legends[tribeName] || "A tribe of legendary vibes and cosmic energy.";
    }
    
    // Quick dialogue for specific moments
    static getQuickDialogue(moment) {
        const dialogues = {
            welcomeBack: "Welcome back, beautiful soul! ✨",
            goodChoice: "Ooh, excellent choice! 👌",
            almostThere: "Almost there, angel! 🌟",
            perfect: "Perfect! Just perfect! 💫",
            thinking: "Let me think about this... 🤔",
            excited: "I'm so excited for you! 🎉",
            impressed: "Damn, you're good at this! 😎",
            mysterious: "The universe works in mysterious ways... 🌌",
            supportive: "You've got this, babe! 💪",
            proud: "I'm so proud of you! 🥹"
        };
        
        return dialogues[moment] || "✨";
    }
}

// Make it globally available
window.NarratorScripts = NarratorScripts;
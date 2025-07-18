/**
 * Angel Initiation Narrator Scripts
 * All dialogue and animation sequences for the emoji narrator
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
                    duration: 4000
                }
            ],
            
            // Greeting phase
            greeting: [
                {
                    frames: ["🙌😄🙌", "👐😁👐", "🤲😊🤲"],
                    speed: 600,
                    text: "Welcome to Green Angel — the sacred space for vibe-certified legends only.",
                    duration: 6000
                },
                {
                    frames: ["🙌😄🙌", "👐😁👐", "🤲😊🤲"],
                    speed: 600,
                    text: "I'm your guide. Here to make your experience even more magical.",
                    duration: 5000
                },
                {
                    frames: ["🙌😄🙌", "👐😁👐", "🤲😊🤲"],
                    speed: 600,
                    text: "Before you float off into the cosmos, I just need a few tiny things from you...",
                    duration: 6000
                },
                {
                    frames: ["🙌😄🙌", "👐😁👐", "🤲😊🤲"],
                    speed: 600,
                    text: "This realm is *invitation only* — for the dopest angels who pass the VIBE CHECK™.",
                    duration: 7000
                },
                {
                    frames: ["🙌😄🙌", "👐😁👐", "🤲😊🤲"],
                    speed: 600,
                    text: "We're here to bring FUN back to the internet. Don't you think?",
                    duration: 5000
                },
                {
                    frames: ["🙌😄🙌", "👐😁👐", "🤲😊🤲"],
                    speed: 600,
                    text: "So... if you're ready, shall we begin?",
                    duration: 4000
                }
            ],
            
            // Angel Identity phase
            angelIdentity: [
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "Ooooh, exciting part — it's time to choose your Angel Identity.",
                    duration: 2500
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "Think of this as your *reverb*... your cosmic codename.",
                    duration: 2500
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "Of course it's cannabis-coded, babe. What else would it be?",
                    duration: 2500
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "But here's the twist...",
                    duration: 1500
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "You only get **FIVE** chances. That's it.",
                    duration: 2000
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "And once you skip a name... it's gone. Like a puff of smoke.",
                    duration: 2500
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "If you feel it — that *YESSS* — lock it in.",
                    duration: 2000
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "Or roll the dice, and tempt fate.",
                    duration: 2000
                },
                {
                    frames: ["👀😏👀", "🙌😳🙌", "🤲😄🤲"],
                    speed: 300,
                    text: "When you're ready... tap ROLL.",
                    duration: 2000
                }
            ],
            
            // Name result reaction
            nameResult: [
                {
                    frames: ["🤯🙌🤯", "👀😄👀", "👐😏👐"],
                    speed: 200,
                    text: "That's pretty fucking sick, if I do say so myself.",
                    duration: 2500
                },
                {
                    frames: ["🤯🙌🤯", "👀😄👀", "👐😏👐"],
                    speed: 200,
                    text: "Looking at you... yeah. That name was *always* yours.",
                    duration: 2500
                },
                {
                    frames: ["🤯🙌🤯", "👀😄👀", "👐😏👐"],
                    speed: 200,
                    text: "Iconic. Let's lock it in.",
                    duration: 2000
                }
            ],
            
            // Tribe sorting intro
            tribeSorting: [
                {
                    frames: ["😲✨😲", "🤯🙌🤯", "👐😱👐"],
                    speed: 250,
                    text: "Now it's time to choose your tribe — your celestial crew.",
                    duration: 2500
                },
                {
                    frames: ["😲✨😲", "🤯🙌🤯", "👐😱👐"],
                    speed: 250,
                    text: "These are called Angel Tribes — each one's a vibe, a frequency, a lifestyle.",
                    duration: 3000
                },
                {
                    frames: ["😲✨😲", "🤯🙌🤯", "👐😱👐"],
                    speed: 250,
                    text: "It's random... kind of. Fate's got a plan. You just pick a bag.",
                    duration: 2500
                },
                {
                    frames: ["😲✨😲", "🤯🙌🤯", "👐😱👐"],
                    speed: 250,
                    text: "This feature's coming soon — but we're planning competitions, leaderboards, races...",
                    duration: 3000
                },
                {
                    frames: ["😲✨😲", "🤯🙌🤯", "👐😱👐"],
                    speed: 250,
                    text: "...and prizes for you *and* your tribe.",
                    duration: 2000
                },
                {
                    frames: ["😲✨😲", "🤯🙌🤯", "👐😱👐"],
                    speed: 250,
                    text: "Now go ahead... shuffle the bag and trust the universe.",
                    duration: 2500
                }
            ],
            
            // Tribe result reaction
            tribeResult: [
                {
                    frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                    speed: 250,
                    text: "Ohhh snap — you're in...",
                    duration: 1500
                },
                {
                    frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                    speed: 250,
                    text: "✨The Holy Smokes✨",
                    duration: 2000
                },
                {
                    frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                    speed: 250,
                    text: "Legend says their leader once hotboxed a dragon's cave and lived to tell the tale.",
                    duration: 3500
                },
                {
                    frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                    speed: 250,
                    text: "You were *born* for this tribe, babe.",
                    duration: 2000
                },
                {
                    frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                    speed: 250,
                    text: "Let's make them proud.",
                    duration: 1500
                }
            ],
            
            // Cosmic alignment (DOB)
            cosmicAlignment: [
                {
                    frames: ["🤲😌🤲", "👐😎👐", "✋🙂✋"],
                    speed: 300,
                    text: "Last bit, promise.",
                    duration: 1500
                },
                {
                    frames: ["🤲😌🤲", "👐😎👐", "✋🙂✋"],
                    speed: 300,
                    text: "I just need your birth month and year — that's it.",
                    duration: 2500
                },
                {
                    frames: ["🤲😌🤲", "👐😎👐", "✋🙂✋"],
                    speed: 300,
                    text: "Not your full birthday — just your cosmic arrival window.",
                    duration: 2500
                },
                {
                    frames: ["🤲😌🤲", "👐😎👐", "✋🙂✋"],
                    speed: 300,
                    text: "It's just so we can keep this space 18+ and sacred.",
                    duration: 2500
                },
                {
                    frames: ["🤲😌🤲", "👐😎👐", "✋🙂✋"],
                    speed: 300,
                    text: "So if you're lying... well, that's on you, babe. 😉",
                    duration: 2500
                }
            ],
            
            // Final blessing
            finalBlessing: [
                {
                    frames: ["🥹👐🥹", "🫶😇🫶", "🤲😌🤲"],
                    speed: 300,
                    text: "Cosmic alignment complete.",
                    duration: 2000
                },
                {
                    frames: ["🥹👐🥹", "🫶😇🫶", "🤲😌🤲"],
                    speed: 300,
                    text: "You're now officially initiated.",
                    duration: 2000
                },
                {
                    frames: ["🥹👐🥹", "🫶😇🫶", "🤲😌🤲"],
                    speed: 300,
                    text: "Welcome home, Green Angel.",
                    duration: 2000
                },
                {
                    frames: ["🥹👐🥹", "🫶😇🫶", "🤲😌🤲"],
                    speed: 300,
                    text: "We've been waiting for you.",
                    duration: 2000
                }
            ],
            
            // Quick reactions for various moments
            rolling: [
                {
                    frames: ["🎲😄🎲", "🎯🤩🎯", "🎰😮🎰"],
                    speed: 150,
                    text: "Ooh, the dice are rolling!",
                    duration: 1500
                }
            ],
            
            shuffling: [
                {
                    frames: ["🌀😵‍💫🌀", "🔮🤯🔮", "✨😱✨"],
                    speed: 200,
                    text: "The cosmic forces are at work!",
                    duration: 2000
                }
            ],
            
            thinking: [
                {
                    frames: ["🤔💭🤔", "👀🧠👀", "💫🤔💫"],
                    speed: 350,
                    text: "Hmm...",
                    duration: 1000
                }
            ],
            
            celebration: [
                {
                    frames: ["🎉😄🎉", "🙌🤩🙌", "✨😁✨"],
                    speed: 200,
                    text: "YES! Absolutely perfect!",
                    duration: 2000
                }
            ]
        };
        
        return scripts[phase] || [];
    }
    
    // Dynamic tribe result with actual tribe name
    static getTribeResultScript(tribeName, tribeEmoji) {
        return [
            {
                frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                speed: 250,
                text: "Ohhh snap — you're in...",
                duration: 1500
            },
            {
                frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                speed: 250,
                text: `✨${tribeName}✨`,
                duration: 2000
            },
            {
                frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                speed: 250,
                text: this.getTribeLegend(tribeName),
                duration: 3500
            },
            {
                frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                speed: 250,
                text: "You were *born* for this tribe, babe.",
                duration: 2000
            },
            {
                frames: ["🫢🤩🫢", "👀😮👀", "🙌🔥🙌"],
                speed: 250,
                text: "Let's make them proud.",
                duration: 1500
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
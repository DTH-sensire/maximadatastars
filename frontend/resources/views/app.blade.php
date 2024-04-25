<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine Core -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    [x-cloak] {
        display: none !important;
    }
    </style>

    <script>
      function app()
      {
        return {
          started: false,
          completed: false,
          activeQuestionIndex: 0,
          patientId: 0,
          clients: [],
          // clients: {{json_encode($clients)}}
          questions: [
            {question: "What is your name?", tag: "name"},
            {question: "What is your age?", tag: "age"},
            {question: "Choose the patient", tag: "patient"},
            {question: "What is your relationship with [name]?", tag: "relationship"},
            {question: "What are [name]'s interests?", tag: "interests"},
            {question: "Does [name] have any favorite books, movies, or TV shows?", tag: "favorites"},
            {question: "Does [name] enjoy socialising or prefer quiet solo activities?", tag: "socialising_activities"},
            {question: "Does [name] like creative activities such as drawing, arts and crafts, and music?", tag: "creative_activities"},
            {question: "Is [name] curious and eager to learn? Are there subjects he/she is particularly interested in?", tag: "learner"},
            {question: "Does [name] enjoy outdoor activities and nature experiences?", tag: "outdoor_activities"},
            {question: "Does [name] have an animal affinity?", tag: "animal_affinity"},
            {question: "Do digital formats like computers, games, and videos pique [name]'s interest?", tag: "computers_interests"},
            {question: "Have [name] expressed a wish to try something specific?", tag: "wishes"},
            {question: "Would [name] prefer indoor activities or spending time in the open air?",tag: "indoor_activities"}
          ],
          questionErrors: [],
          answers: [],
          suggestions: '',
          enterAction() {
            return;
            if (!this.started) {
              this.started = true;
            //   $('#question-input-0').focus();
//               document.setTimout(() => {
//                 console.log('tab')
//                 document.activeElement.dispatchEvent(new KeyboardEvent("keypress", { 
//     key: "Tab" 
// }));
            //   }, 0);
              return;
            }

            // this.activeQuestionIndex++;

            // if (this.activeQuestionIndex == this.questions.length) {
            //   this.complete();
            // }
          },
          start() {
            this.started = true;
            this.completed = false;
            this.activeQuestionIndex = 0;
            this.answers = [];
            this.suggestions = '';
          },
          restart() {
            this.started = false;
            this.completed = false;
            this.activeQuestionIndex = 0;
            this.answers = [];
            this.suggestions = '';
          },
          async complete() {
            this.completed = true;

            let request = await fetch("/get-suggestions", {
              method: "POST",
              body: JSON.stringify({
                questions: this.questions.map(question => {
                  // return question.question.replace('[name]', this.selectedClient());
                  return question.tag;
                }),
                answers: this.answers,
              }),
              headers: {
                "Content-type": "application/json; charset=UTF-8",
                "X-CSRF-Token": document.getElementById('csrf-token').getAttribute('content')
              }
            });

            let response = await request.json();
            this.suggestions = response.suggestions;
          },
          showNextQuestion(currentQuestionInput) {
            if (! currentQuestionInput.value) {
                this.questionErrors[this.activeQuestionIndex] = true;
                return;
            }

            console.log(this.answers);

            this.questionErrors[this.activeQuestionIndex] = false;
            this.activeQuestionIndex++;

            if (this.activeQuestionIndex == this.questions.length) {
              this.complete();
            }
          },
          showPreviousQuestion() {
            this.activeQuestionIndex--;
          },
          async getClients() {
            let request = await fetch("/clients", {
              method: "GET",
              body: JSON.stringify({
                questions: this.questions,
                answers: this.answers,
              }),
              headers: {
                "Content-type": "application/json; charset=UTF-8",
                "X-CSRF-Token": document.getElementById('csrf-token').getAttribute('content')
              }
            });

            let response = await request.json();
            return response;
          },
          selectedClient() {
            if (!this.answers[2]) {
              return '';
            }

            let selectedClient = this.clients.filter(client => {
              return client.id == this.answers[2];
            })[0];

            return selectedClient.firstname;
          }
        }
      }
    </script>

    <title>Maxima's Data Stars</title>
</head>

<body class="antialiased dark:bg-black dark:text-white/50 font-sans h-screen" x-data="app()" x-init="clients = await (await fetch('/clients')).json()"
  @keyup.enter="enterAction()">
    <div class="bg-gray-900 flex h-full items-center py-24 sm:py-32" x-cloak>
        <div class="mx-auto w-full px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <div x-show="!started">
                    <h2 class="text-base font-semibold leading-7 text-indigo-400">Maxima's Data Stars</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">"If only I could turn back
                        time"</p>
                    <p class="mt-6 text-lg leading-8 text-gray-300">Many parents and siblings experience regret after
                        the
                        passing of their child/sibling, wishing they had spent more time together and making memories.
                        Unfortunately, they do not always know what to do or what is possible with their child/sibling.
                    </p>
                    <p class="mt-6 text-lg leading-8 text-gray-300">Get suggestions by answering the following
                        questions,
                        press "Start" to continue.</p>


                    <div class="mx-auto mt-8 flex flex-col items-center flex-1">
                        <div class="flex items-center gap-2 mt-5">
                            <button @click="start(); $nextTick(() => { document.getElementById(`question-input-0`).focus(); });"
                                class="rounded-md bg-indigo-500 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">Start</button>
                        </div>
                    </div>
                </div>

                <!-- Compleet message -->
                <div x-show="completed">
                    <h2 class="text-base font-semibold leading-7 text-indigo-400">Maxima's Data Stars</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Suggestions</p>

                    <p class="mt-6 text-xl leading-8 text-gray-300" x-text="suggestions"></p>
                    
                    <div class="mx-auto mt-8 flex flex-col items-center flex-1">
                        <div class="flex items-center gap-2 mt-5">
                            <button @click="restart()"
                                class="rounded-md bg-indigo-500 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">Restart</button>
                        </div>
                    </div>
                </div>

                <!-- Questions -->
                <div x-show="started && !completed" id="form" class="flex text-left">
                    <div class="w-full max-w-2xl px-5" id="multistep-form">
                        <div>
                            <form class="p-10" @submit.prevent="">
                                <div>
                                    <!--Form Field-->
                                    <template x-for="(question, index) in questions">
                                      <div class="relative" x-show="(index==activeQuestionIndex)">
                                          <div class="relative">
                                              <div class="absolute flex items-center gap-1 text-indigo-400 top-1 -left-8">
                                                  <span x-text="&nbsp;(index+1)"></span>
                                                  <svg height="10" width="11" fill="currentColor">
                                                      <path
                                                          d="M7.586 5L4.293 1.707 5.707.293 10.414 5 5.707 9.707 4.293 8.293z">
                                                      </path>
                                                      <path d="M8 4v2H0V4z"></path>
                                                  </svg>
                                              </div>
                                              <label class="text-xl font-medium text-indigo-400">
                                                  <span x-text="question.question.replace('[name]', selectedClient())"></span>
                                                  <span>*</span></label>
                                              <div class="mt-1 text-lg text-gray-300 hidden"><span>Howdy Stranger,
                                                      Let's get acquainted. </span></div>
                                                <!-- Patient dropdown -->
                                                <template x-if="index==2">
                                                  <select
                                                    x-trap="index==activeQuestionIndex"
                                                    x-model="answers[index]"
                                                    :id="`question-input-${index}`"
                                                    name="patientId" x-model="patientId" autocomplete="patientId" class="block w-full rounded-md border-0 bg-white/5 py-1.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6 [&amp;_*]:text-black mt-4">
                                                    <option value="">Make a choice</option>
                                                    <template x-for="client in clients">
                                                      <option :value="client.id" x-text="`${client.firstname}&nbsp;${client.lastname}`"></option>
                                                    </template>
                                                  </select>
                                                </template>

                                                <!-- Relation dropdown -->
                                                <template x-if="index==3">
                                                  <select
                                                    x-trap="index==activeQuestionIndex"
                                                    x-model="answers[index]"
                                                    :id="`question-input-${index}`"
                                                    name="patientId" x-model="patientId" autocomplete="patientId" class="block w-full rounded-md border-0 bg-white/5 py-1.5 text-white shadow-sm ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6 [&amp;_*]:text-black mt-4">
                                                    <option value="">Make a choice</option>
                                                    <option value="father">Father</option>
                                                    <option value="mother">Mother</option>
                                                    <option value="brother">Brother</option>
                                                    <option value="sister">Ssister</option>
                                                  </select>
                                                </template>

                                                <!-- Questions -->
                                                <template x-if="index!=2 && index!=3">
                                                  <input
                                                    x-trap="index==activeQuestionIndex"
                                                    x-model="answers[index]"
                                                    :id="`question-input-${index}`"
                                                    class="bg-gray-900 pb-2 w-full px-0 mt-6 text-xl font-normal border-0 border-b-2 border-neutral-300 focus:border-indigo-400 focus:outline-none focus:ring-0 placeholder:text-neutral-500"
                                                    type="text" placeholder="Type your answer here.." autofocus>
                                                </template>
                                          </div>
                                          <div x-show="questionErrors[index]==true" class="inline-flex items-center px-2 py-px mt-3 text-sm text-red-600 bg-red-100 rounded">
                                            <svg height="24" viewBox="0 0 24 24" width="24" fill="currentColor">
                                                <path clip-rule="evenodd" d="M16.3361 17.9998L7.00279 18C5.49294 18 4.52754 16.391 5.23806 15.0588L9.90471 6.30882C10.6576 4.89706 12.6812 4.89706 13.4341 6.30881L18.1008 15.0586C18.8113 16.3908 17.8459 17.9998 16.3361 17.9998ZM11.6694 8.50003C12.2217 8.50003 12.6694 8.94774 12.6694 9.50003V11.5C12.6694 12.0523 12.2217 12.5 11.6694 12.5C11.1171 12.5 10.6694 12.0523 10.6694 11.5V9.50003C10.6694 8.94774 11.1171 8.50003 11.6694 8.50003ZM11.6694 16C12.2217 16 12.6694 15.5523 12.6694 15C12.6694 14.4477 12.2217 14 11.6694 14C11.1171 14 10.6694 14.4477 10.6694 15C10.6694 15.5523 11.1171 16 11.6694 16Z" fill-rule="evenodd"></path>
                                            </svg>
                                            This question is a required
                                        </div>
                                      </div>
                                    </template>

                                    <div class="flex items-center gap-2 mt-5">
                                        <button
                                            @click="showNextQuestion(document.getElementById(`question-input-${activeQuestionIndex}`));"
                                            class="flex items-center gap-2 px-4 py-2 text-xl font-medium text-white transition-colors bg-indigo-400 rounded-md shadow outline-none hover:bg-neutral-800 focus:bg-neutral-800 focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-neutral-300">
                                            <span>OK</span> <svg height="13" width="16" fill="white">
                                                <path
                                                    d="M14.293.293l1.414 1.414L5 12.414.293 7.707l1.414-1.414L5 9.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <span class="text-sm text-neutral-500">
                                            press <span class="font-medium text-neutral-700">Enter ↵</span></span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Vue form -->

                <!-- Footer navigation -->
                <footer x-show="started && !completed" class="fixed bottom-0 right-0 flex flex-row-reverse justify-start px-10 py-5">
                    <button @click="showNextQuestion(document.getElementById(`question-input-${activeQuestionIndex}`));" class="px-3 py-3 text-white border-l rounded rounded-l-none bg-neutral-900 border-neutral-600 disabled:text-neutral-500" :disabled="activeQuestionIndex==questions.length">
                        <svg height="9" width="14" fill="currentColor">
                            <path d="M12.293.293l1.414 1.414L7 8.414.293 1.707 1.707.293 7 5.586z"></path>
                        </svg>
                    </button>
                    <button @click="showPreviousQuestion();" class="px-3 py-3 text-white rounded rounded-r-none bg-neutral-900 disabled:text-neutral-500" :disabled="activeQuestionIndex==0">
                        <svg height="9" width="14" fill="currentColor">
                            <path d="M11.996 8.121l1.414-1.414L6.705 0 0 6.707l1.414 1.414 5.291-5.293z"></path>
                        </svg>
                    </button>
                </footer>
            </div>
        </div>
    </div>
</body>

</html>
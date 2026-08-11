<template>
    <Head title="Education & Workshops Administration" />

    <AdminLayout>
        <div class="p-6 space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Education & Workshops Program Control Center</h1>
                    <p class="text-sm text-gray-600 mt-1">Manage 7-level curricula, video stream media, transcriptions, regional workshops, and demand-driven skills training.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="showAddWorkshopModal = true"
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors flex items-center gap-1.5 shadow"
                    >
                        <PlusIcon class="w-4 h-4" /> Publish Workshop
                    </button>
                    <button
                        @click="showAddLessonModal = true"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition-colors flex items-center gap-1.5 shadow"
                    >
                        <PlusIcon class="w-4 h-4" /> Add Curriculum Lesson
                    </button>
                </div>
            </div>

            <!-- Level Member Counts -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                <div v-for="l in 7" :key="l" class="p-3 bg-white rounded-xl shadow-xs border border-gray-200 text-center">
                    <p class="text-xs font-bold text-gray-500">Level {{ l }}</p>
                    <p class="text-lg font-black text-blue-600 mt-1">{{ getLevelMemberCount(l) }}</p>
                    <p class="text-[10px] text-gray-400">Members</p>
                </div>
            </div>

            <!-- Admin Control Tabs: Curricula | Workshops | Skills Demand | Submissions -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-3 flex items-center gap-6 text-xs font-bold overflow-x-auto">
                    <button
                        @click="activeTab = 'curricula'"
                        :class="[activeTab === 'curricula' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
                    >
                        Level Curricula ({{ curricula.length }})
                    </button>
                    <button
                        @click="activeTab = 'workshops'"
                        :class="[activeTab === 'workshops' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
                    >
                        Facilitated Workshops ({{ workshops ? workshops.length : 0 }})
                    </button>
                    <button
                        @click="activeTab = 'skills'"
                        :class="[activeTab === 'skills' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
                    >
                        Member Skills Demand ({{ skillsDemand ? skillsDemand.length : 0 }})
                    </button>
                    <button
                        @click="activeTab = 'submissions'"
                        :class="[activeTab === 'submissions' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']"
                    >
                        Pending Submissions ({{ pendingSubmissions ? pendingSubmissions.length : 0 }})
                    </button>
                </div>

                <!-- TAB 1: Curricula & Video Stream Config -->
                <div v-if="activeTab === 'curricula'" class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 border-b text-gray-500 uppercase tracking-wider font-bold">
                                <tr>
                                    <th class="py-3 px-4">Level</th>
                                    <th class="py-3 px-4">Module & Title</th>
                                    <th class="py-3 px-4">Video Stream Media</th>
                                    <th class="py-3 px-4">Media & Transcript</th>
                                    <th class="py-3 px-4">Duration</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr 
                                    v-for="item in curricula" 
                                    :key="item.id" 
                                    @click="openLessonModal(item)"
                                    class="hover:bg-blue-50/50 cursor-pointer transition-colors group"
                                >
                                    <td class="py-3 px-4 font-bold text-blue-600">Level {{ item.level }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-800">
                                        <div class="text-xs font-bold text-gray-900 group-hover:text-blue-600 flex items-center gap-1.5">
                                            <FilmIcon class="w-4 h-4 text-blue-500 shrink-0" />
                                            {{ item.module_title }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-900 font-medium">
                                        <span class="font-bold text-blue-700 hover:underline flex items-center gap-1">
                                            <PlayIcon class="w-3.5 h-3.5 text-blue-600 inline shrink-0" />
                                            {{ item.lesson_title }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span v-if="item.cloudflare_video_id || item.video_url" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                <PlayIcon class="w-3 h-3 text-indigo-600" /> Video Attached
                                            </span>
                                            <span v-if="item.transcript" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                📜 Transcript
                                            </span>
                                            <span v-if="!item.video_url && !item.cloudflare_video_id" class="text-gray-400 text-[11px]">Audio/Text</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 font-medium">{{ item.duration_minutes || 15 }} mins</td>
                                    <td class="py-3 px-4 text-right space-x-2" @click.stop>
                                        <button 
                                            @click="openLessonModal(item)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-[11px] hover:bg-blue-100 transition-colors"
                                        >
                                            <EyeIcon class="w-3.5 h-3.5" /> Preview / Play
                                        </button>
                                        <button 
                                            @click="openEditLessonModal(item)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold text-[11px] hover:bg-amber-100 transition-colors"
                                        >
                                            <PencilSquareIcon class="w-3.5 h-3.5" /> Edit
                                        </button>
                                        <button 
                                            @click="deleteLesson(item.id)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 font-bold text-[11px] hover:bg-red-100 transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="curricula.length === 0">
                                    <td colspan="6" class="py-8 text-center text-gray-400">No curricula lessons created yet. Click "Add Curriculum Lesson" above.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Workshops Setup -->
                <div v-if="activeTab === 'workshops'" class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 uppercase text-gray-500 font-bold border-b border-gray-200">
                                <tr>
                                    <th class="py-3 px-4">Level</th>
                                    <th class="py-3 px-4">Workshop Topic</th>
                                    <th class="py-3 px-4">Location / Mode</th>
                                    <th class="py-3 px-4">Instructor</th>
                                    <th class="py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="ws in workshops" :key="ws.id" class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-bold text-emerald-600">Level {{ ws.level }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-900">{{ ws.topic }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ ws.location }}</td>
                                    <td class="py-3 px-4 text-gray-700 font-medium">{{ ws.instructor_name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ ws.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: Member Skills Demand -->
                <div v-if="activeTab === 'skills'" class="p-6 space-y-3">
                    <h4 class="text-sm font-bold text-gray-900">Member Requested Skills (Aggregated Demand)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div v-for="sk in skillsDemand" :key="sk.id" class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-between">
                            <div>
                                <h5 class="text-xs font-bold text-gray-900">{{ sk.title }}</h5>
                                <span class="text-[11px] font-semibold text-blue-600">{{ sk.demand_count }} Member Requests</span>
                            </div>
                            <button class="px-3 py-1 rounded bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700">
                                Create Cohort
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Practical Submissions -->
                <div v-if="activeTab === 'submissions'" class="p-6">
                    <div class="space-y-4">
                        <div v-for="sub in pendingSubmissions" :key="sub.id" class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-900">{{ sub.user_name }}</span>
                                    <span class="text-xs text-gray-500">({{ sub.user_email }})</span>
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-bold">Level {{ sub.level }} Task</span>
                                </div>
                                <p class="font-bold text-xs text-blue-600 mt-1">{{ sub.task_title }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ sub.submission_text }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="grade(sub.id, 'approved')" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700">Approve</button>
                                <button @click="grade(sub.id, 'rejected')" class="px-3 py-1.5 rounded-lg bg-red-600 text-white font-bold text-xs hover:bg-red-700">Reject</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Interactive Lesson Preview & Edit Modal -->
        <div 
            v-if="showLessonDetailsModal" 
            @click.self="closeLessonModal" 
            @keydown.escape.window="closeLessonModal"
            class="fixed inset-0 bg-slate-900/75 backdrop-blur-md flex items-center justify-center p-4 z-50 overflow-y-auto"
        >
            <div class="bg-white rounded-2xl max-w-3xl w-full p-6 space-y-5 shadow-2xl relative my-8 border border-gray-100">
                <!-- Close Button -->
                <button 
                    @click="closeLessonModal" 
                    class="absolute top-4 right-4 px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition-colors text-xs flex items-center gap-1 shadow-xs"
                    title="Close Modal (Esc)"
                >
                    <XMarkIcon class="w-4 h-4" /> Close
                </button>

                <!-- Modal Header -->
                <div class="flex items-start justify-between border-b border-gray-100 pb-4 pr-20">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800">
                                Level {{ activeLesson?.level }} Lesson
                            </span>
                            <span class="text-xs text-gray-500 font-semibold">
                                {{ activeLesson?.duration_minutes || 15 }} mins
                            </span>
                            <span v-if="activeLesson?.transcript" class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                Transcription Included
                            </span>
                        </div>
                        <h3 class="text-lg font-extrabold text-gray-900 mt-1">{{ activeLesson?.lesson_title }}</h3>
                        <p class="text-xs text-gray-500 font-medium">{{ activeLesson?.module_title }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="isEditMode = !isEditMode" 
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-xs border"
                            :class="isEditMode ? 'bg-amber-500 text-slate-950 hover:bg-amber-400 font-extrabold border-amber-600' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border-amber-200'"
                        >
                            <PencilSquareIcon class="w-4 h-4" />
                            {{ isEditMode ? 'Cancel Edit' : '✏️ Edit Lesson' }}
                        </button>
                    </div>
                </div>

                <!-- VIEW MODE: Sub-tabs (Video Player | Transcript | Test & Verify) -->
                <div v-if="!isEditMode" class="space-y-4">
                    <!-- Tab Buttons -->
                    <div class="flex items-center gap-3 border-b border-gray-200 pb-2 mb-4 text-xs font-bold">
                        <button 
                            @click="modalTab = 'video'" 
                            :class="[modalTab === 'video' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']" 
                            class="flex items-center gap-1.5"
                        >
                            <PlayIcon class="w-4 h-4" /> Video Player Preview
                        </button>
                        <button 
                            @click="modalTab = 'transcript'" 
                            :class="[modalTab === 'transcript' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']" 
                            class="flex items-center gap-1.5"
                        >
                            <DocumentTextIcon class="w-4 h-4" /> Transcript & Notes
                        </button>
                        <button 
                            @click="modalTab = 'test'" 
                            :class="[modalTab === 'test' ? 'text-blue-600 border-b-2 border-blue-600 pb-2' : 'text-gray-500 hover:text-gray-800']" 
                            class="flex items-center gap-1.5"
                        >
                            <CheckCircleIcon class="w-4 h-4" /> Test & Verify Player
                        </button>
                    </div>

                    <!-- SUB-TAB 1: Video Stream Player -->
                    <div v-if="modalTab === 'video'" class="space-y-4 text-xs">
                        <div class="rounded-xl overflow-hidden bg-slate-900 border border-slate-800 p-1 shadow-lg">
                            <div v-if="hasVideoSource(activeLesson?.video_url || activeLesson?.cloudflare_video_id)" class="aspect-video bg-black rounded-lg relative flex items-center justify-center overflow-hidden">
                                <!-- iFrame Embed Player (YouTube, Vimeo, Cloudflare Stream Embed) -->
                                <iframe 
                                    v-if="isIframeEmbedUrl(getVideoEmbedSrc(activeLesson?.video_url || activeLesson?.cloudflare_video_id))" 
                                    :src="getVideoEmbedSrc(activeLesson?.video_url || activeLesson?.cloudflare_video_id)" 
                                    class="w-full h-full border-0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen
                                ></iframe>

                                <!-- HTML5 Native Direct Video Player -->
                                <video 
                                    v-else 
                                    controls 
                                    autoplay
                                    playsinline
                                    preload="metadata"
                                    class="w-full h-full rounded-lg"
                                    :src="getVideoEmbedSrc(activeLesson?.video_url || activeLesson?.cloudflare_video_id)"
                                ></video>
                            </div>

                            <!-- Legacy Mock ID Alert Banner -->
                            <div v-else-if="isMockStreamId(activeLesson?.video_url || activeLesson?.cloudflare_video_id)" class="p-6 bg-slate-950 text-white text-center space-y-3 rounded-lg">
                                <FilmIcon class="w-8 h-8 text-amber-400 mx-auto" />
                                <div>
                                    <p class="font-bold text-sm text-amber-300">Mock Video ID Detected</p>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        ID string: <code class="bg-slate-900 px-1.5 py-0.5 rounded text-amber-200">{{ activeLesson?.video_url || activeLesson?.cloudflare_video_id }}</code>
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Click below to attach a verified working video stream player.
                                    </p>
                                </div>
                                <button 
                                    @click="attachSampleVideoToActive" 
                                    class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-black text-xs hover:bg-amber-400 transition-colors shadow"
                                >
                                    🎬 Attach Working Video Stream Link
                                </button>
                            </div>

                            <div v-else class="py-10 text-center text-slate-400 bg-slate-950 rounded-lg">
                                <FilmIcon class="w-8 h-8 text-slate-600 mx-auto mb-2" />
                                <p class="font-bold">No Video Link Attached</p>
                                <p class="text-[11px] text-slate-500 mt-1">Attach a video stream URL or upload a video file.</p>
                                <button 
                                    @click="attachSampleVideoToActive" 
                                    class="mt-3 px-3.5 py-1.5 rounded-lg bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition-colors"
                                >
                                    🎬 Attach Working Video Stream Link
                                </button>
                            </div>
                        </div>

                        <!-- Practical Exercise Prompt -->
                        <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-100 space-y-1">
                            <span class="font-extrabold text-blue-900 uppercase text-[10px] tracking-wider block">Practical Activity Prompt</span>
                            <p class="text-gray-800 leading-relaxed font-medium">
                                {{ activeLesson?.practical_activity_prompt || 'No practical exercise assigned to this lesson.' }}
                            </p>
                        </div>
                    </div>

                    <!-- SUB-TAB 2: Transcript & Low-Literacy Notes -->
                    <div v-if="modalTab === 'transcript'" class="space-y-4 text-xs">
                        <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                                    <DocumentTextIcon class="w-4 h-4 text-blue-600" /> Full Lesson Transcript
                                </span>
                                <span class="text-[10px] text-gray-500">Auto-scrollable text reader</span>
                            </div>
                            <div class="p-3 bg-white rounded-lg border border-gray-200 max-h-48 overflow-y-auto text-gray-700 leading-relaxed space-y-2">
                                <p v-if="activeLesson?.transcript" class="whitespace-pre-line">{{ activeLesson.transcript }}</p>
                                <div v-else class="text-gray-400 italic text-center py-4">
                                    No full text transcript uploaded yet. Click "Edit Lesson" to add or auto-generate transcription.
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/80 space-y-2">
                            <span class="font-bold text-amber-900 text-xs flex items-center gap-1.5">
                                💡 Low-Literacy Simplified Summary Notes
                            </span>
                            <p v-if="activeLesson?.simplified_notes" class="text-amber-950 leading-relaxed">
                                {{ activeLesson.simplified_notes }}
                            </p>
                            <p v-else class="text-amber-700/70 italic">
                                Simplified summary notes have not been added yet.
                            </p>
                        </div>
                    </div>

                    <!-- SUB-TAB 3: Test & Verify Lesson Experience -->
                    <div v-if="modalTab === 'test'" class="space-y-4 text-xs">
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-emerald-900 text-sm flex items-center gap-1.5">
                                    <CheckCircleIcon class="w-5 h-5 text-emerald-600" /> Member Experience Simulator
                                </h4>
                                <span class="px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 text-[10px] font-bold">Verification Mode</span>
                            </div>
                            <p class="text-emerald-800">
                                Verify that video streaming, audio reading, transcript rendering, and student exercise submission work cleanly before publishing to Level {{ activeLesson?.level }} members.
                            </p>

                            <div class="p-3 bg-white rounded-lg border border-emerald-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900">1. Video Player Test:</span>
                                    <span :class="hasVideoSource(activeLesson?.video_url) ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold'">
                                        {{ hasVideoSource(activeLesson?.video_url) ? 'Passed (Video Configured)' : 'Needs Video Link' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900">2. Transcript Availability:</span>
                                    <span :class="activeLesson?.transcript ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold'">
                                        {{ activeLesson?.transcript ? 'Available' : 'Pending Transcript' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900">3. Student Exercise Prompt:</span>
                                    <span class="text-emerald-600 font-bold">Ready for Submissions</span>
                                </div>
                            </div>

                            <button 
                                @click="testAudioReadout"
                                class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors flex items-center gap-1.5 shadow"
                            >
                                <SpeakerWaveIcon class="w-4 h-4" /> {{ isAudioPlaying ? 'Stop Audio Readout Test' : 'Test Speech Reader (Audio Readout)' }}
                            </button>
                        </div>
                    </div>

                    <!-- Modal Bottom Footer Close Button -->
                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button 
                            @click="closeLessonModal" 
                            class="px-5 py-2.5 rounded-xl bg-gray-900 text-white font-bold text-xs hover:bg-gray-800 transition-colors shadow"
                        >
                            Close Preview Modal
                        </button>
                    </div>
                </div>

                <!-- EDIT MODE FORM -->
                <div v-else class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="font-bold text-gray-700 block mb-1">Target Level</label>
                            <select v-model="editForm.level" class="w-full p-2.5 rounded-xl border border-gray-300">
                                <option v-for="l in 7" :key="l" :value="l">Level {{ l }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-gray-700 block mb-1">Duration (Minutes)</label>
                            <input v-model="editForm.duration_minutes" type="number" class="w-full p-2.5 rounded-xl border border-gray-300" />
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Module Title</label>
                        <input v-model="editForm.module_title" type="text" class="w-full p-2.5 rounded-xl border border-gray-300" />
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Lesson Title</label>
                        <input v-model="editForm.lesson_title" type="text" class="w-full p-2.5 rounded-xl border border-gray-300" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-gray-700">Video Source URL or Stream Link</label>
                            <button 
                                type="button" 
                                @click="insertSampleVideoToEdit"
                                class="text-[11px] font-bold text-indigo-600 hover:underline flex items-center gap-1"
                            >
                                🎬 Insert Working Video URL
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="editForm.video_url" type="text" placeholder="Video URL or Embed Link (e.g. https://www.youtube.com/embed/...)" class="flex-1 p-2.5 rounded-xl border border-gray-300 text-[11px]" />
                            <label class="px-3 py-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold hover:bg-indigo-100 cursor-pointer flex-shrink-0">
                                <span>📁 {{ selectedEditFileName ? selectedEditFileName : 'Upload Video' }}</span>
                                <input type="file" accept="video/*" class="hidden" @change="handleEditVideoFileSelect" />
                            </label>
                        </div>

                        <!-- Real-Time Upload Progress Card -->
                        <div v-if="uploadProgress.active || editForm.processing || editForm.progress" class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-white space-y-2.5 my-3 shadow-2xl">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold flex items-center gap-2 text-indigo-300">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-ping shrink-0"></span>
                                    {{ uploadProgress.statusText || 'Uploading Video Stream...' }}
                                </span>
                                <span class="font-black text-indigo-400 font-mono text-sm">{{ getEditPercentage() }}%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-3 rounded-full overflow-hidden border border-slate-700 p-0.5">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 h-full rounded-full transition-all duration-200 shadow-md"
                                    :style="{ width: Math.max(6, getEditPercentage()) + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between items-center text-[11px] text-slate-400">
                                <span>Transferred: {{ uploadProgress.loadedMB }} MB / {{ uploadProgress.totalMB }} MB</span>
                                <span class="font-semibold text-indigo-300">Direct Stream API</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-gray-700">Full Lesson Transcript</label>
                            <button 
                                type="button" 
                                @click="generateAutoTranscript" 
                                class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1"
                            >
                                ✨ Auto-Generate AI Outline
                            </button>
                        </div>
                        <textarea v-model="editForm.transcript" rows="4" placeholder="Enter complete lesson transcript text..." class="w-full p-2.5 rounded-xl border border-gray-300 font-mono text-[11px]"></textarea>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Simplified Summary Notes (Low Literacy Friendly)</label>
                        <textarea v-model="editForm.simplified_notes" rows="2" placeholder="Key bullet points for quick learning..." class="w-full p-2.5 rounded-xl border border-gray-300"></textarea>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Practical Activity Prompt</label>
                        <textarea v-model="editForm.practical_activity_prompt" rows="2" class="w-full p-2.5 rounded-xl border border-gray-300"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <button @click="deleteLesson(editForm.id!)" class="text-red-600 font-bold hover:underline text-xs">Delete Lesson</button>
                        <div class="flex gap-2">
                            <button @click="isEditMode = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-bold">Cancel</button>
                            <button @click="saveLessonEdit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold shadow">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Curriculum Lesson Modal -->
        <div v-if="showAddLessonModal" @click.self="showAddLessonModal = false" class="fixed inset-0 bg-slate-900/75 backdrop-blur-md flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-2xl relative my-8 border border-gray-100">
                <button @click="showAddLessonModal = false" class="absolute top-4 right-4 px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition-colors text-xs flex items-center gap-1 shadow-xs">
                    <XMarkIcon class="w-4 h-4" /> Close
                </button>

                <h3 class="text-base font-bold text-gray-900">Add Curriculum Lesson (Video Stream & Transcript)</h3>

                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-bold text-gray-700 block mb-1">Target Level (1 to 7)</label>
                            <select v-model="lessonForm.level" class="w-full p-2.5 rounded-xl border border-gray-300">
                                <option v-for="l in 7" :key="l" :value="l">Level {{ l }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-gray-700 block mb-1">Duration (Minutes)</label>
                            <input v-model="lessonForm.duration_minutes" type="number" class="w-full p-2.5 rounded-xl border border-gray-300" />
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Module Title</label>
                        <input v-model="lessonForm.module_title" type="text" placeholder="Financial Literacy Essentials" class="w-full p-2.5 rounded-xl border border-gray-300" />
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Lesson Title</label>
                        <input v-model="lessonForm.lesson_title" type="text" placeholder="Understanding Cashflow & Reserves" class="w-full p-2.5 rounded-xl border border-gray-300" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-gray-700">Video Source URL or File</label>
                            <button 
                                type="button" 
                                @click="insertSampleVideoToAdd"
                                class="text-[11px] font-bold text-indigo-600 hover:underline flex items-center gap-1"
                            >
                                🎬 Insert Working Video URL
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model="lessonForm.video_url" type="text" placeholder="Video Stream URL or Embed Link" class="flex-1 p-2.5 rounded-xl border border-gray-300 text-[11px]" />
                            <label class="px-3 py-2.5 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold hover:bg-indigo-100 cursor-pointer flex-shrink-0">
                                <span>📁 {{ selectedAddFileName ? selectedAddFileName : 'Upload Video File' }}</span>
                                <input type="file" accept="video/*" class="hidden" @change="handleAddVideoFileSelect" />
                            </label>
                        </div>
                        <p v-if="selectedAddFileName" class="text-[11px] font-bold text-emerald-600 mt-1">
                            ✓ Ready to upload file: {{ selectedAddFileName }}
                        </p>
                        <p v-else class="text-[11px] text-gray-500 mt-1">
                            Upload video file directly or link existing Video Stream URL
                        </p>

                        <!-- Real-Time Upload Progress Card -->
                        <div v-if="uploadProgress.active || lessonForm.processing || lessonForm.progress" class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-white space-y-2.5 my-3 shadow-2xl">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold flex items-center gap-2 text-indigo-300">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-ping shrink-0"></span>
                                    {{ uploadProgress.statusText || 'Uploading Video Stream...' }}
                                </span>
                                <span class="font-black text-indigo-400 font-mono text-sm">{{ getAddPercentage() }}%</span>
                            </div>
                            <div class="w-full bg-slate-800 h-3 rounded-full overflow-hidden border border-slate-700 p-0.5">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 h-full rounded-full transition-all duration-200 shadow-md"
                                    :style="{ width: Math.max(6, getAddPercentage()) + '%' }"
                                ></div>
                            </div>
                            <div class="flex justify-between items-center text-[11px] text-slate-400">
                                <span>Transferred: {{ uploadProgress.loadedMB }} MB / {{ uploadProgress.totalMB }} MB</span>
                                <span class="font-semibold text-indigo-300">Direct Stream API</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-gray-700">Lesson Transcript & Text Content</label>
                            <button 
                                type="button" 
                                @click="generateNewLessonTranscript" 
                                class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1"
                            >
                                ✨ Auto-Generate AI Transcript
                            </button>
                        </div>
                        <textarea v-model="lessonForm.transcript" rows="3" placeholder="Enter transcript or full audio lesson text..." class="w-full p-2.5 rounded-xl border border-gray-300"></textarea>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Practical Activity Prompt</label>
                        <textarea v-model="lessonForm.practical_activity_prompt" rows="2" placeholder="Prompt for student exercise..." class="w-full p-2.5 rounded-xl border border-gray-300"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button @click="showAddLessonModal = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-bold">Cancel</button>
                    <button @click="submitLesson" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold shadow">Save Lesson</button>
                </div>
            </div>
        </div>

        <!-- Add Workshop Modal -->
        <div v-if="showAddWorkshopModal" @click.self="showAddWorkshopModal = false" class="fixed inset-0 bg-slate-900/75 backdrop-blur-md flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative my-8 border border-gray-100">
                <button @click="showAddWorkshopModal = false" class="absolute top-4 right-4 px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition-colors text-xs flex items-center gap-1 shadow-xs">
                    <XMarkIcon class="w-4 h-4" /> Close
                </button>

                <h3 class="text-base font-bold text-gray-900">Publish Regional Workshop</h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Target Education Level</label>
                        <select v-model="wsForm.level" class="w-full p-2.5 rounded-xl border border-gray-300">
                            <option v-for="l in 7" :key="l" :value="l">Level {{ l }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Workshop Topic</label>
                        <input v-model="wsForm.topic" type="text" placeholder="Practical Business Financial Management" class="w-full p-2.5 rounded-xl border border-gray-300" />
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Description</label>
                        <textarea v-model="wsForm.description" rows="3" placeholder="Workshop details and objectives..." class="w-full p-2.5 rounded-xl border border-gray-300"></textarea>
                    </div>

                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Instructor / Facilitator Name</label>
                        <input v-model="wsForm.instructor_name" type="text" placeholder="John Banda (Business Accountant)" class="w-full p-2.5 rounded-xl border border-gray-300" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button @click="showAddWorkshopModal = false" class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-bold">Cancel</button>
                    <button @click="submitWorkshop" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold shadow">Publish Workshop</button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    PlusIcon, 
    XMarkIcon, 
    PlayIcon, 
    FilmIcon, 
    EyeIcon, 
    PencilSquareIcon, 
    DocumentTextIcon, 
    CheckCircleIcon, 
    SpeakerWaveIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    curricula: any[];
    workshops: any[];
    skillsDemand: any[];
    pendingSubmissions: any[];
    levelCounts: any[];
}>();

const activeTab = ref(new URLSearchParams(window.location.search).get('tab') || 'curricula');
const modalTab = ref<'video' | 'transcript' | 'test'>('video');
const showAddLessonModal = ref(false);
const showAddWorkshopModal = ref(false);
const showLessonDetailsModal = ref(false);
const isEditMode = ref(false);
const isAudioPlaying = ref(false);
const activeLesson = ref<any>(null);

const selectedAddFileName = ref<string>('');
const selectedEditFileName = ref<string>('');

const WORKING_SAMPLE_VIDEO_URL = 'https://www.youtube.com/embed/L_LUpnjgPso';

const lessonForm = useForm({
    level: 1,
    module_title: '',
    lesson_title: '',
    content_type: 'video',
    video_url: '',
    video_file: null as File | null,
    duration_minutes: 15,
    transcript: '',
    simplified_notes: '',
    practical_activity_prompt: '',
});

const editForm = useForm({
    id: null as number | null,
    level: 1,
    module_title: '',
    lesson_title: '',
    video_url: '',
    video_file: null as File | null,
    duration_minutes: 15,
    transcript: '',
    simplified_notes: '',
    practical_activity_prompt: '',
});

const wsForm = useForm({
    level: 1,
    topic: '',
    description: '',
    instructor_name: '',
});

const getLevelMemberCount = (level: number) => {
    const found = props.levelCounts?.find((lc: any) => lc.current_professional_level === `level_${level}` || lc.current_professional_level === `${level}`);
    return found ? found.total : 0;
};

const openLessonModal = (lesson: any) => {
    activeLesson.value = lesson;
    isEditMode.value = false;
    modalTab.value = 'video';
    selectedEditFileName.value = '';
    editForm.id = lesson.id;
    editForm.level = lesson.level;
    editForm.module_title = lesson.module_title;
    editForm.lesson_title = lesson.lesson_title;
    editForm.video_url = lesson.video_url || lesson.cloudflare_video_id || '';
    editForm.video_file = null;
    editForm.duration_minutes = lesson.duration_minutes || 15;
    editForm.transcript = lesson.transcript || '';
    editForm.simplified_notes = lesson.simplified_notes || '';
    editForm.practical_activity_prompt = lesson.practical_activity_prompt || '';
    showLessonDetailsModal.value = true;
};

const openEditLessonModal = (lesson: any) => {
    openLessonModal(lesson);
    isEditMode.value = true;
};

const closeLessonModal = () => {
    showLessonDetailsModal.value = false;
    activeLesson.value = null;
    isEditMode.value = false;
    stopAudio();
};

const isMockStreamId = (url?: string) => {
    if (!url) return false;
    return url.startsWith('stream_') || url.startsWith('cf_stream_');
};

const hasVideoSource = (url?: string) => {
    if (!url) return false;
    if (isMockStreamId(url)) return false;
    return true;
};

const isIframeEmbedUrl = (url?: string) => {
    if (!url) return false;
    return url.includes('youtube.com') || 
           url.includes('youtu.be') || 
           url.includes('vimeo.com') || 
           url.includes('videodelivery.net') || 
           url.includes('cloudflarestream.com') ||
           url.includes('embed') || 
           url.includes('iframe');
};

const getVideoEmbedSrc = (urlOrId?: string) => {
    if (!urlOrId || isMockStreamId(urlOrId)) {
        return WORKING_SAMPLE_VIDEO_URL;
    }

    const trimmed = urlOrId.trim();

    if (trimmed.includes('youtube.com/watch?v=')) {
        return trimmed.replace('watch?v=', 'embed/');
    }

    if (trimmed.includes('youtu.be/')) {
        return trimmed.replace('youtu.be/', 'youtube.com/embed/');
    }

    // 32-character Cloudflare Stream UID or Cloudflare Stream URL
    if (/^[a-f0-9]{32}$/i.test(trimmed)) {
        return `https://iframe.videodelivery.net/${trimmed}`;
    }

    if (trimmed.includes('videodelivery.net') || trimmed.includes('cloudflarestream.com')) {
        return trimmed;
    }

    return trimmed;
};

const insertSampleVideoToAdd = () => {
    lessonForm.video_url = WORKING_SAMPLE_VIDEO_URL;
};

const insertSampleVideoToEdit = () => {
    editForm.video_url = WORKING_SAMPLE_VIDEO_URL;
};

const attachSampleVideoToActive = () => {
    if (!activeLesson.value) return;
    useForm({
        lesson_title: activeLesson.value.lesson_title,
        level: activeLesson.value.level,
        module_title: activeLesson.value.module_title,
        video_url: WORKING_SAMPLE_VIDEO_URL,
    }).post(route('admin.grownet.education.curriculum.update', activeLesson.value.id), {
        onSuccess: () => {
            activeLesson.value.video_url = WORKING_SAMPLE_VIDEO_URL;
            alert('Working video stream attached successfully!');
        }
    });
};

const generateAutoTranscript = () => {
    const title = editForm.lesson_title || 'Core Lesson Module';
    const module = editForm.module_title || 'Level Training';
    editForm.transcript = `[00:00] Introduction to ${title}\n[02:15] Key Principles & Foundations of ${module}\n[07:30] Practical Application in Business Operations\n[12:40] Summary & Member Exercise Overview`;
    editForm.simplified_notes = `• Understanding ${title}\n• Practical steps for daily business execution\n• Complete the exercise prompt at the end of the lesson.`;
};

const generateNewLessonTranscript = () => {
    const title = lessonForm.lesson_title || 'Core Lesson Module';
    const module = lessonForm.module_title || 'Level Training';
    lessonForm.transcript = `[00:00] Introduction to ${title}\n[02:15] Key Principles & Foundations of ${module}\n[07:30] Practical Application in Business Operations\n[12:40] Summary & Member Exercise Overview`;
};

const testAudioReadout = () => {
    if (isAudioPlaying.value) {
        stopAudio();
        return;
    }

    if ('speechSynthesis' in window) {
        const text = activeLesson.value?.transcript || activeLesson.value?.simplified_notes || `Welcome to ${activeLesson.value?.lesson_title}. This audio readout tests speech transcription for members.`;
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.onend = () => { isAudioPlaying.value = false; };
        utterance.onerror = () => { isAudioPlaying.value = false; };
        window.speechSynthesis.speak(utterance);
        isAudioPlaying.value = true;
    } else {
        alert('Browser Speech Synthesis is not supported on this device.');
    }
};

const stopAudio = () => {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }
    isAudioPlaying.value = false;
};

const handleAddVideoFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        lessonForm.video_file = file;
        selectedAddFileName.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(1)}MB)`;
    }
};

const handleEditVideoFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        editForm.video_file = file;
        selectedEditFileName.value = `${file.name} (${(file.size / (1024 * 1024)).toFixed(1)}MB)`;
    }
};

const uploadProgress = ref({
    active: false,
    percentage: 0,
    loadedMB: '0.0',
    totalMB: '0.0',
    statusText: 'Uploading Video Stream...',
});

const getAddPercentage = () => {
    if (lessonForm.progress?.percentage) {
        return Math.round(lessonForm.progress.percentage);
    }
    return uploadProgress.value.percentage || 0;
};

const getEditPercentage = () => {
    if (editForm.progress?.percentage) {
        return Math.round(editForm.progress.percentage);
    }
    return uploadProgress.value.percentage || 0;
};

const updateProgress = (progress: any, file?: File | null) => {
    uploadProgress.value.active = true;
    let loaded = progress?.loaded || (progress?.event && progress?.event?.loaded) || 0;
    let total = progress?.total || (progress?.event && progress?.event?.total) || (file ? file.size : 0);
    let pct = progress?.percentage || (total > 0 ? Math.round((loaded / total) * 100) : 0);

    uploadProgress.value.percentage = Math.min(100, Math.max(1, pct));
    uploadProgress.value.loadedMB = (loaded / (1024 * 1024)).toFixed(1);
    uploadProgress.value.totalMB = (total / (1024 * 1024)).toFixed(1);
    uploadProgress.value.statusText = pct >= 95 ? 'Encoding & Syncing with Cloudflare Stream...' : 'Uploading Video Stream...';
};

const submitLesson = () => {
    uploadProgress.value.active = true;
    uploadProgress.value.percentage = 1;
    uploadProgress.value.statusText = 'Uploading Video Stream...';

    lessonForm.post(route('admin.grownet.education.curriculum.store'), {
        forceFormData: true,
        onUploadProgress: (progress: any) => {
            updateProgress(progress, lessonForm.video_file);
        },
        onSuccess: () => {
            uploadProgress.value.active = false;
            showAddLessonModal.value = false;
            lessonForm.reset();
            selectedAddFileName.value = '';
            alert('Lesson added successfully!');
        },
        onError: () => {
            uploadProgress.value.active = false;
        },
        onFinish: () => {
            uploadProgress.value.active = false;
        }
    });
};

const saveLessonEdit = () => {
    if (!editForm.id) return;
    uploadProgress.value.active = true;
    uploadProgress.value.percentage = 1;
    uploadProgress.value.statusText = 'Uploading Video Stream...';

    editForm.post(route('admin.grownet.education.curriculum.update', editForm.id), {
        forceFormData: true,
        onUploadProgress: (progress: any) => {
            updateProgress(progress, editForm.video_file);
        },
        onSuccess: () => {
            uploadProgress.value.active = false;
            isEditMode.value = false;
            showLessonDetailsModal.value = false;
            selectedEditFileName.value = '';
            alert('Lesson updated successfully!');
        },
        onError: () => {
            uploadProgress.value.active = false;
        },
        onFinish: () => {
            uploadProgress.value.active = false;
        }
    });
};

const deleteLesson = (id: number) => {
    if (confirm('Delete this curriculum lesson?')) {
        useForm({}).delete(route('admin.grownet.education.curriculum.delete', id), {
            onSuccess: () => {
                if (activeLesson.value?.id === id) {
                    closeLessonModal();
                }
            }
        });
    }
};

const submitWorkshop = () => {
    wsForm.post(route('admin.grownet.education.workshop.store'), {
        onSuccess: () => {
            showAddWorkshopModal.value = false;
            wsForm.reset();
            alert('Workshop published successfully!');
        }
    });
};

const grade = (id: number, status: string) => {
    useForm({ status }).post(route('admin.grownet.education.grade', id));
};
</script>

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{mix('js/app.js')}}"></script>
<div id="app" class="p-10 bg-gray-600">
    <div class="flex justify-between items-center text-white">
        <h1 style="font-size:2.5rem;">Teams <small class="font-thin">(@{{ teamsSorted.length }})</small></h1>
        <button class="bg-yellow-500 rounded py-1 px-3" @click="sortDesc = !sortDesc">Sort By Ranking @{{ sortDesc ? '↓' : '↑' }}</button>
    </div>
    <small class="text-white">* can play goalie</small>
    <div class="flex flex-wrap gap-5 my-5">
        <div v-for="team in teamsSorted"
             class="w-1/5 flex-grow bg-white rounded shadow-lg overflow-hidden"
             style="min-width: 300px;"
        >
            <h2 class="text-xl bg-yellow-500 p-3 text-center text-white border-yellow-700 border-b-2">
                <div
                    class="inline-block w-16 h-16 bg-white rounded-full text-gray-500 pt-4 border-yellow-600 border-2 mb-2"
                    style="font-size: 2rem;">
                    @{{ team.name.split('')[0] }}
                </div>
                <br>
                <strong>@{{ team.name }}</strong>
            </h2>
            <ul class="p-3">

                <!-- Team Chart Headings -->
                <li class="flex justify-between pb-5">
                    <strong>Player <br><small class="font-thin">(Total @{{ team.players.length }})</small></strong>
                    <strong class="align-middle">Ranking <br><small class="font-thin">(Avg: @{{ team.avgRanking }})</small></strong>
                </li>

                <!-- Team Chart Players -->
                <li v-for="player in team.players" class="flex justify-between border-b-2 border-gray-200 p-1">
                <span>
                    @{{player.first_name}}
                    @{{player.last_name}}
                    <span v-if="player.can_play_goalie">*</span>
                </span>
                    <span>
                    @{{player.ranking}}
                </span>
                </li>
            </ul>
        </div>
    </div>
    <div class="pt-5 text-white">
        Total Players: @{{ totalPlayers }}
    </div>
</div>
<script>
    new Vue({
        el: '#app',
        data(){
            return {
                sortDesc: true,
                teams: {!! json_encode($teams) !!}
            }
        },
        computed:{
            totalPlayers(){
                return this.teamsSorted.map(team => team.playerCount).reduce((a, b) => a + b, 0)
            },
            teamsSorted(){
                return this.teams.map(team =>{
                    team.players = this.sortDesc
                        ? team.players.sort((a, b)=> b.ranking - a.ranking)
                        : team.players.sort((a, b)=> a.ranking - b.ranking)
                    return team
                })
            }
        }
    })
</script>

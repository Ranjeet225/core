<?php
namespace App\Traits;
use App\Constants\Status;

trait SpinWheel {
    public function playSpinWheel($game, $request) {
        $validator = $this->investValidation($request, 'red,blue');

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $user     = auth()->user();
        $fallback = $this->fallback($request, $user, $game);

        if (@$fallback['error']) {
            return response()->json($fallback);
        }

        $random = mt_rand(0, 100);

        if ($random <= $game->probable_win) {
            $win    = Status::WIN;
            $result = $request->choose;
        } else {
            $win    = Status::LOSS;
            $result = $request->choose == 'blue' ? 'red' : 'blue';
        }

        $invest = $this->invest($user, $request, $game, $result, $win);

        $res['game_id'] = $invest['game_log']->id;
        $res['invest']  = $request->invest;
        $res['result']  = $result;
        $res['balance'] = $user->balance;
        return response()->json($res);
    }

    public function gameEndSpinWheel($game, $request) {
        $validator = $this->endValidation($request);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $gameLog = $this->runningGame();

        if (!$gameLog) {
            return response()->json(['error' => 'Game Logs not found']);
        }

        $res = $this->gameResult($game, $gameLog);

        return response()->json($res);
    }
}